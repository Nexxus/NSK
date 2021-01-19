<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\EntityManager;
use AppBundle\Repository\ProductRepository;
use AppBundle\Entity\ProductType;
use AppBundle\Entity\Attribute;
use AppBundle\Entity\AttributeOption;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductAttributeRelation;
use AppBundle\Entity\ProductAttributeFile;

/**
 * This command syncs one direction: From NSK to PrestaShop
 * Modifications of objects in Prestashop will be undone by NSK
 */
class PrestaShopCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'nexxus:prestashop';

    private $baseUrl;
    private $key;
    private $em;
    private $webService;
    private $mappings;

    protected function configure()
    {
        $this
            ->setDescription('Sends products to PrestaShop.')
            ->setHelp('This command will send available products to PrestaShop')
            ->addArgument('productStatusIdFilter', InputArgument::REQUIRED, 'Products with this status id are send to PrestaShop');

        $this->mappings = array(
                'categories' => [
                    'name' => 'name',
                    'description' => 'comment',
                    'position' => 'pindex',
                    'id_parent' => function () { return 2; }, // 1=root, 2=Home
                    'active' => function (ProductType $productType) { return 1; },
                    'link_rewrite' => function (ProductType $productType) { return urlencode(str_replace([' ','/'], '_', strtolower($productType->getName()))); },
                ],
                'product_features' => [
                    'name' => 'name'
                ],
                'product_feature_values' => [
                    'value' => function ($o) { 
                        $value = is_a($o, AttributeOption::class) ? $o->getName() : $o->getValue(); 
                        return str_replace(['=', '>', '<'], '_', $value);
                    },
                    'custom' => function ($o) { return is_a($o, AttributeOption::class) ? 0 : 1; },
                    'id_feature' => function ($o) { return $o->getAttribute()->getExternalId(); },
                ],
                'products' => [
                    'name' => 'name',
                    'description_short' => 'name',
                    'description' => 'description',
                    'price' => function (Product $p) { return round(($p->getPrice() / 1.21), 6); },
                    'reference' => function (Product $p) { return 'NSK-' . $p->getSku(); },
                    'type' => function (Product $p) { return 'simple'; },
                    'state' => function (Product $p) { return 1; },
                    'active' => function (Product $p) { return 1; },
                    'id_tax_rules_group' => function (Product $p) { return 1; },
                    'id_shop_default' => function (Product $p) { return 1; },
                    'id_category_default' => function (Product $p) { return $p->getType()->getExternalId(); },
                ],
                'stock_availables' => [
                    'quantity' => function (Product $p) { return $p->getQuantitySaleable(); },
                ]                                                               
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
        To clear database:
        1. update attribute set external_id=null;update attribute_option set external_id=null;update product_type set external_id=null;update product set external_id=null;update product_attribute set external_id=null;
        2. Prestahop cleaner module
        3. Publish this file
        4. https://nexxus.eco/nsk-test/prestashopcommand
        */
        
        $isDebug = $this->getContainer()->get('kernel')->isDebug();

        if ($isDebug) {
            $this->key = "2UA45ZCDGECH3XPTWEHSBS14TM2I5QEC";
            $this->baseUrl = "http://shop.mediapoints.nl/";
        }
        else {
            $this->key = 'ZAZIIVE5M7XC8C22NDTLE7UJ26T9LCIV';
            $this->baseUrl = 'http://www.mediapoints.nl/';
        }

        $this->webService = new \PrestaShopWebservice($this->baseUrl, $this->key, $isDebug);

        /** @var EntityManager */
        $this->em = $this->getContainer()->get('doctrine')->getManager(); 

        $this->createResources("categories", $this->em->getRepository('AppBundle:ProductType')->findAll());
        $this->createResources("product_features", $this->em->getRepository('AppBundle:Attribute')->findBy(['type' => [0,1], 'isPublic' => true]));
        $this->createResources("product_feature_values", $this->em->getRepository('AppBundle:Attribute')->findAttributeOptionsForApi());

        $productStatusId = $input->getArgument('productStatusIdFilter');
        $products = $this->em->getRepository('AppBundle:Product')->findBy(['status' => $productStatusId]);

        $this->createResources("products", $products);
        $this->createResources("stock_availables", $products, false);
        $this->createImages($products);

        $output->writeln("Done!");
    }

    /**
     * Creates or updates resources of all kinds 
     */ 
    private function createResources($resourceName, array $collection, $useBlankXmlOnUpdate = true)
    {
        if (!count($collection)) return;

        $resourceSingularName = substr($resourceName, -3) == "ies" ? substr($resourceName, 0, -3)."y" : substr($resourceName, 0, -1);

        foreach ($collection as $object)
        {
            try
            {
                $externalId = (int)$object->getExternalId();
                
                if ($resourceName == "stock_availables")
                {
                    if (!$externalId) throw new \Exception("When creating stocks, its product object should have external ID");
                    $productXml = $this->webService->get(['resource' => 'products', 'id' => $externalId]);
                    $externalId = (int)$productXml->product->children()->associations->stock_availables->stock_available->id;
                    if (!$externalId) throw new \Exception("When creating stocks, the stock_available object should have external ID");
                }   

                // if you get vague error when getting, debug function executeRequest in PSWebServiceLibrary.php
                if ($useBlankXmlOnUpdate || !$externalId)
                    $xml = $this->webService->get(['url' => $this->baseUrl . 'api/' . $resourceName . '?schema=blank']);
                else
                    $xml = $this->webService->get(['resource' => $resourceName, 'id' => $externalId]);

                $xmlFields = $xml->{$resourceSingularName}->children();             

                foreach ($this->mappings[$resourceName] as $xmlFieldName => $objectFieldName)
                {
                    if (is_callable($objectFieldName))
                    {
                        $value = call_user_func($objectFieldName, $object);
                    }
                    else 
                    {
                        $getter = 'get' . ucfirst($objectFieldName);
                        $value = $object->{$getter}();
                    }

                    if ($xmlFields->{$xmlFieldName}->language)
                        $xmlFields->{$xmlFieldName}->language = $value;
                    else 
                        $xmlFields->{$xmlFieldName} = $value;
                }

                // product associations
                if ($resourceName == "products")
                {
                    $this->createProductAssociations($object, $xmlFields);
                }

                // edit or add on API
                if ($externalId)
                {
                    $xmlFields->id = $externalId;
                    $this->webService->edit(['resource' => $resourceName, 'putXml' => $xml->asXML(), 'id' => $externalId]);    
                }
                else
                {
                    $createdXml = $this->webService->add(['resource' => $resourceName, 'postXml' => $xml->asXML()]);           
                    $externalId = (int)$createdXml->{$resourceSingularName}->children()->id;
                    $object->setExternalId($externalId);
                    $this->em->flush();
                }
            }
            catch (\PrestaShopWebserviceException $e)
            {
                // If you want to know more about the error, change define('_PS_MODE_DEV_', true); in config/defines.inc.php in the shop. 
                // And then look in the response in the body.
                throw $e;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }

    private function createProductAssociations(Product $product, \SimpleXMLElement $xmlFields)
    {
        // set category of product
        $xmlFields->associations->categories->addChild('category')->id = $product->getType()->getExternalId();
                    
        // set attributes cq product features of product
        /** @var ProductAttributeRelation $par */
        foreach ($product->getAttributeRelations() as $par)
        {
            switch ($par->getAttribute()->getType())
            {
                case Attribute::TYPE_SELECT:
                    if (!$par->getSelectedOption()) continue;
                    $feature = $xmlFields->associations->product_features->addChild('product_feature');
                    $feature->id = $par->getAttribute()->getExternalId();
                    $feature->id_feature_value = $par->getSelectedOption()->getExternalId();
                    break;
                case Attribute::TYPE_TEXT:
                    if (!$par->getValue()) continue;
                    $this->createResources("product_feature_values", array($par));
                    $feature = $xmlFields->associations->product_features->addChild('product_feature');
                    $feature->id = $par->getAttribute()->getExternalId();
                    $feature->id_feature_value = $par->getExternalId();
                    break;                            
                default:
            }                   
        }
    }

    private function createImages(array $products)
    {
        foreach ($products as $product)
        {                
            /** @var ProductAttributeRelation $par */
            foreach ($product->getAttributeRelations() as $par)
            {
                if ($par->getAttribute()->getType() == Attribute::TYPE_FILE)
                {
                    foreach ($par->getFiles() as $file)
                    {
                        if (!$file->getExternalId())
                            $this->uploadImage($file);
                    }
                }                    
            }
        }
    }

    private function uploadImage(ProductAttributeFile $file)
    {
        $shopImageUrl = $this->baseUrl . 'api/images/products/' . $file->getProduct()->getExternalId() . '/';
        $myPath = $this->getContainer()->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'var'. DIRECTORY_SEPARATOR .'uploads' . DIRECTORY_SEPARATOR;   
        $myImagePath = $myPath . $file->getOriginalClientFilename();

        copy($myPath . $file->getUniqueServerFilename(), $myImagePath);

        $myImageMime = explode('.', $myImagePath);
        $myImageMime = 'image/' . end($myImageMime);

        $args['image'] = new \CurlFile($myImagePath, $myImageMime);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_URL, $shopImageUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->key.':');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        unlink($myImagePath);

        if ($httpCode != 200) {
            throw new \Exception("Image upload failed");
        }

        $xml = new \SimpleXMLElement(substr($result, strpos($result, "<?xml")));
        $externalId = (int)$xml->image->id;
        $file->setExternalId($externalId);
        $this->em->flush();
        return $externalId;
    }
}