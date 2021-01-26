<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\ProductType;
use AppBundle\Entity\Attribute;
use AppBundle\Entity\AttributeOption;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductAttributeRelation;
use AppBundle\Entity\ProductAttributeFile;
use AppBundle\Entity\Customer;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\ProductOrderRelation;

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
    /** @var EntityManager */
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
                // XML TARGET => OBJECT SOURCE
                'categories' => [
                    'name' => 'name',
                    'description' => 'comment',
                    'position' => 'pindex',
                    'id_parent' => function () { return 2; }, // 1=root, 2=Home
                    'active' => function (ProductType $productType) { return 1; },
                    'link_rewrite' => function (ProductType $productType) { return rawurlencode(str_replace([' ','/'], '_', strtolower($productType->getName()))); },
                ],
                'product_features' => [
                    'name' => 'name'
                ],
                'product_feature_values' => [
                    'value' => function ($o) { 
                        if (is_a($o, AttributeOption::class))
                            $value = $o->getName();
                            // or $o is ProductAttributeRelation
                        elseif ($o->getAttribute()->getType() == Attribute::TYPE_PRODUCT)
                            $value = $o->valueProduct()->getName(); 
                        else
                            $value = $o->getValue(); 
                        return str_replace(['=', '>', '<'], '_', $value);
                    },
                    'custom' => function ($o) { return is_a($o, AttributeOption::class) ? 0 : 1; },
                    'id_feature' => function ($o) { return $o->getAttribute()->getExternalId(); },
                ],
                'products' => [
                    'name' => 'name',
                    'description_short' => 'name',
                    'description' => 'description',
                    'link_rewrite' => function (Product $p) { return rawurlencode(str_replace([' ','/'], '_', strtolower($p->getName()))); },
                    'price' => function (Product $p) { return round(($p->getPrice() / 1.21), 6); },
                    'reference' => function (Product $p) { return 'NSK-' . $p->getSku(); },
                    'type' => function (Product $p) { return 'simple'; },
                    'state' => function (Product $p) { return 1; },
                    'active' => function (Product $p) { return 1; },
                    'show_price' => function (Product $p) { return 1; },
                    'minimal_quantity' => function (Product $p) { return 1; },
                    'available_for_order' => function (Product $p) { return 1; },
                    'id_tax_rules_group' => function (Product $p) { return 1; },
                    'id_shop_default' => function (Product $p) { return 1; },
                    'id_category_default' => function (Product $p) { return $p->getType()->getExternalId(); },
                ],
                'stock_availables' => [
                    'quantity' => function (Product $p) { return $p->getQuantitySaleable(); },
                    // other properties are loaded from API
                ],

                // OBJECT TARGET => XML SOURCE
                'customers' => [
                    'externalId' => 'id',
                    'name' => function (\SimpleXMLElement $xml) { 
                        if ((string)$xml->A0_company)
                            return (string)$xml->A0_company;
                        else if ((string)$xml->A1_company)
                            return (string)$xml->A1_company;                          
                        else
                            return trim($xml->firstname . ' ' . $xml->lastname); 
                    },
                    'representative' => function (\SimpleXMLElement $xml) { 
                        if ((string)$xml->A0_company || (string)$xml->A1_company)
                            return trim($xml->firstname . ' ' . $xml->lastname);                        
                        else
                            return ""; 
                    },
                    'street' => function (\SimpleXMLElement $xml) { return trim($xml->A0_address1 . ' ' . $xml->A0_address2); },
                    'zip' => 'A0_postcode',
                    'city' => 'A0_city',
                    'street2' => function (\SimpleXMLElement $xml) { return trim($xml->A1_address1 . ' ' . $xml->A1_address2); },
                    'zip2' => 'A1_postcode',
                    'city2' => 'A1_city',
                    'phone' => 'phone',
                    'phone2' => 'phone_mobile',
                    'email' => 'email'
                ],
                'orders' => [
                    'externalId' => 'id',
                    'orderNr' => function (\SimpleXMLElement $xml) { return ('PS-'.$xml->reference); },
                    'orderDate' => function (\SimpleXMLElement $xml) { return new \DateTime($xml->date_add); },
                    'discount' => 'total_discounts',
                    'transport' => 'total_shipping',
                    'customer' => function (\SimpleXMLElement $xml) { return $this->em->getRepository(Customer::class)->findOneBy(['externalId' => $xml->id_customer]); },
                    'remarks' => function (\SimpleXMLElement $xml) { return "Betalingswijze: ". $xml->payment; },
                    'isGift' => function (\SimpleXMLElement $xml) { return $xml->gift === "1" ? true : false; },
                    'status' => function (\SimpleXMLElement $xml) { return $this->em->getRepository('AppBundle:OrderStatus')->findOrCreate("Bestelling ingelezen uit webshop", false, true); },
                ],
                'order_details' => [
                    'externalId' => 'id',
                    'quantity' => 'product_quantity',
                    'price' => 'unit_price_tax_incl'
                ]                
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
        To clear database:
        1. update update nexxus.afile set external_id=null;acompany set external_id=null;update aorder set external_id=null;update product_order set external_id=null;update attribute set external_id=null;update attribute_option set external_id=null;update product_type set external_id=null;update product set external_id=null;update product_attribute set external_id=null;
        2. Prestahop cleaner module
        3. Publish this file
        4. https://nexxus.eco/nsk-test/prestashopcommand
        */
        
        //$isDebug = $this->getContainer()->get('kernel')->isDebug();
        $isDebug = gethostname() === "YOGA";
        $isDebug = false;

        if ($isDebug) {
            $this->key = "2UA45ZCDGECH3XPTWEHSBS14TM2I5QEC";
            $this->baseUrl = "http://shop.mediapoints.nl/";
        }
        else {
            $this->key = 'ZAZIIVE5M7XC8C22NDTLE7UJ26T9LCIV';
            $this->baseUrl = 'http://www.mediapoints.nl/';
        }

        $this->webService = new \PrestaShopWebservice($this->baseUrl, $this->key, $isDebug);
        $this->em = $this->getContainer()->get('doctrine')->getManager(); 
        $productStatusId = $input->getArgument('productStatusIdFilter');

        $this->createResources("categories", $this->em->getRepository(ProductType::class)->findAll(), false);
        $this->createResources("product_features", $this->em->getRepository(Attribute::class)->findBy(['type' => [0,1], 'isPublic' => true]));
        $this->createResources("product_feature_values", $this->em->getRepository(Attribute::class)->findAttributeOptionsForApi());

        $products = $this->em->getRepository(Product::class)->findBy(['status' => $productStatusId]);

        $this->createResources("products", $products);
        $this->createImages($products);

        $this->cleanResources("categories", ProductType::class);
        $this->cleanResources("product_features", Attribute::class, ['isPublic' => true]);
        $this->cleanResources("products", Product::class, ['status' => $productStatusId]);

        $this->loadResources("customers", Customer::class);
        $this->loadResources("orders", SalesOrder::class);
        $this->loadResources("order_details", ProductOrderRelation::class);

        $this->createResources("stock_availables", $products, false);

        $output->writeln("Done!");
    }

    #region CREATE

    /**
     * Creates or updates resources of all kinds 
     */ 
    private function createResources($resourceName, array $collection, $useBlankXmlOnUpdate = true)
    {
        if (!count($collection)) return;

        $resourceSingularName = $this->getSingular($resourceName);

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
                {
                    try 
                    {
                        $xml = $this->webService->get(['resource' => $resourceName, 'id' => $externalId]);
                    }
                    catch (\PrestaShopWebserviceException $e)
                    {
                        // 404 error, so PrestaShop is cleaned!
                        $externalId = null;
                        $xml = $this->webService->get(['url' => $this->baseUrl . 'api/' . $resourceName . '?schema=blank']);
                    }
                }

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
                $this->logError("createResources", $e);
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
            if ($par->getExternalId() == 201)
                $debug=true;
            
            switch ($par->getAttribute()->getType())
            {
                case Attribute::TYPE_SELECT:
                    if (!$par->getSelectedOption()) break;
                    $feature = $xmlFields->associations->product_features->addChild('product_feature');
                    $feature->id = $par->getAttribute()->getExternalId();
                    $feature->id_feature_value = $par->getSelectedOption()->getExternalId();
                    break;
                case Attribute::TYPE_TEXT:
                    if (!$par->getValue()) break;
                    $this->createResources("product_feature_values", array($par), false);
                    $feature = $xmlFields->associations->product_features->addChild('product_feature');
                    $feature->id = $par->getAttribute()->getExternalId();
                    $feature->id_feature_value = $par->getExternalId();
                    break;   
                case Attribute::TYPE_PRODUCT:
                    if (!$par->getValueProduct()) break;
                    $this->createResources("product_feature_values", array($par), false);
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

    #endregion

    #region CLEAN AND LOAD

    private function cleanResources($resourceName, $entityName, array $entityCriteria = [])
    {
        try 
        {
            $xml = $this->webService->get(['resource' => $resourceName]);
            $resources = $xml->{$resourceName}->children();

            foreach ($resources as $resource) 
            {
                $attributes = $resource->attributes();
                $externalId = (int)$attributes['id'];
                $entityCriteria['externalId'] = $externalId;

                $object = $this->em->getRepository($entityName)->findOneBy($entityCriteria);

                if (!$object)
                {
                    $this->webService->delete(['resource' => $resourceName, 'id' => $externalId]);
                }
            }
        } 
        catch (\PrestaShopWebserviceException $ex) 
        {
            $this->logError("cleanResources", $ex);
        }
    }

    private function loadResources($resourceName, $entityName)
    {
        $resourceSingularName = $this->getSingular($resourceName);

        try 
        {
            $xmlList = $this->webService->get(['resource' => $resourceName]);
            $resources = $xmlList->{$resourceName}->children();
            $externalId = null;

            foreach ($resources as $resource) 
            {
                $attributes = $resource->attributes();
                $externalId = (int)$attributes['id'];
                $updated = false;

                $xml = $this->webService->get(['resource' => $resourceName, 'id' => $externalId]);
                $xmlFields = $xml->{$resourceSingularName}->children();

                if ($resourceName == "customers")
                {
                    $this->loadAddresses($xmlFields);
                }

                $object = $this->em->getRepository($entityName)->findOneBy(['externalId' => $externalId]);

                if (!$object)
                {
                    if ($resourceName == "order_details")
                    {
                        $o = $this->em->getRepository(SalesOrder::class)->findOneBy(['externalId' => $xmlFields->id_order]);
                        $p = $this->em->getRepository(Product::class)->findOneBy(['externalId' => $xmlFields->product_id]);
                        if (!$p || !$o) continue;
                        $object = new ProductOrderRelation($p, $o);
                    }
                    else
                        $object = new $entityName();
                    
                    $this->em->persist($object);
                }

                foreach ($this->mappings[$resourceName] as $objectFieldName => $xmlFieldName)
                {
                    $getter = 'get' . ucfirst($objectFieldName);
                    $oldValue = $object->{$getter}();
                    
                    if (is_callable($xmlFieldName))
                    {
                        $value = call_user_func($xmlFieldName, $xmlFields);
                    }
                    else 
                    {
                        if ($xmlFields->{$xmlFieldName}->language)
                            $value = (string)$xmlFields->{$xmlFieldName}->language;
                        else 
                            $value = (string)$xmlFields->{$xmlFieldName};
                    }

                    if ($value != $oldValue)
                    {
                        $setter = 'set' . ucfirst($objectFieldName);
                        $object->{$setter}($value);
                        $updated = true;
                    }
                }

                if ($updated)
                    $this->em->flush();
            }
        } 
        catch (\PrestaShopWebserviceException $ex) 
        {
            $this->logError("loadResources", $ex);
        }
    }

    private function loadAddresses(\SimpleXMLElement $customerFields)
    {
        $xmlList = $this->webService->get(['resource' => 'addresses', 'display' => 'full', 'filter[id_customer]' => '['.$customerFields->id.']']);
        $addresses = $xmlList->addresses->children();

        for ($i = 0; $i <= 1; $i++) 
        {
            if (!is_a($addresses[$i], \SimpleXMLElement::class)) break;
            
            foreach ($addresses[$i] as $key => $val)
            {
                $customerFields->{'A' . $i . '_' . $key} = (string)$val[0];
            }
        }
    }

    #endregion

    private function getSingular($word)
    {
        return substr($word, -3) == "ies" ? substr($word, 0, -3)."y" : substr($word, 0, -1);
    }

    private function logError($function, \Exception $ex)
    {
        /** @var $logger LoggerInterface */
        $logger = $this->getContainer()->get('logger');
        $logger->error("PrestaShopCommand error in function '" . $function . "' with exception: ". $ex->__toString());
    }
}