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

class PrestaShopCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'nexxus:prestashop';

    private $baseUrl;
    private $key;
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
                    'id_parent' => function () { return 2; },
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
                    'id_product' => 'externalId',
                    'id_product_attribute' => function (Product $p) { return 0; },
                    'id_shop' => function (Product $p) { return 1; },
                    'id_shop_group' => function (Product $p) { return 0; },
                    'depends_on_stock' => function (Product $p) { return 0; },
                    'out_of_stock' => function (Product $p) { return 2; },
                    'quantity' => function (Product $p) { return $p->getQuantitySaleable(); },
                    'location' => function (Product $p) { return $p->getLocation() ? $p->getLocation()->getName() : ""; },
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
        3. https://nexxus.eco/nsk-test/prestashopcommand
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
        $em = $this->getContainer()->get('doctrine')->getManager(); 

        $this->createResources("categories", $em->getRepository('AppBundle:ProductType')->findBy(['externalId' => null]));
        $this->createResources("product_features", $em->getRepository('AppBundle:Attribute')->findBy(['externalId' => null, 'type' => [0,1]]));
        $this->createResources("product_feature_values", $em->getRepository('AppBundle:Attribute')->findAttributeOptionsWithoutExternalId());

        $productStatusId = $input->getArgument('productStatusIdFilter');
        $products = $em->getRepository('AppBundle:Product')->findWebshopSelection($productStatusId);
        $this->createResources("products", $products);
        //$this->createResources("stock_availables", $products); dit moet met update/edit/put
        $this->createImages($products);

        $em->flush();

        $output->writeln("Done!");
    }

    private function createResources($resourceName, array $collection)
    {
        if (!count($collection)) return;

        $resourceSingularName = $resourceName == "categories" ? "category" : substr($resourceName, 0, -1);

        foreach ($collection as $object)
        {
            try
            {
                // if you get vague error when getting, debug function executeRequest in PSWebServiceLibrary.php
                $blankXml = $this->webService->get(['url' => $this->baseUrl . 'api/' . $resourceName . '?schema=blank']);
                $xmlFields = $blankXml->{$resourceSingularName}->children();

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
                    /** @var Product */
                    $product = $object;
                    $xmlFields->associations->categories->addChild('category')->id = $product->getType()->getExternalId();
                    
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

                $createdXml = $this->webService->add(['resource' => $resourceName, 'postXml' => $blankXml->asXML()]);
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
            
            $newExternalId = (int)$createdXml->{$resourceSingularName}->children()->id;
            
            $object->setExternalId($newExternalId);
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
        return $externalId;
    }
}