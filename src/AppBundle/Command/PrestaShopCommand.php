<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\EntityManager;
use AppBundle\Repository\ProductRepository;

class PrestaShopCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'nexxus:prestashop';

    protected function configure()
    {
        $this
            ->setDescription('Sends products to PrestaShop.')
            ->setHelp('This command will send available products to PrestaShop')
            ->addArgument('productStatusIdFilter', InputArgument::REQUIRED, 'Products with this status id are send to PrestaShop');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager */
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        
        /** @var ProductRepository */
        $repo = $em->getRepository('AppBundle:Product');

        $kernel = $this->getContainer()->get('kernel');

        $productStatusId = $input->getArgument('productStatusIdFilter');

        $products = $repo->findWebshopSelection($productStatusId);

        $webService = new \PrestaShopWebservice('http://www.mediapoints.nl/', 'ZAZIIVE5M7XC8C22NDTLE7UJ26T9LCIV', $kernel->isDebug());

        // if you get vague error, debug function executeRequest in PSWebServiceLibrary.php
        $blankXml = $webService->get(['url' => 'http://www.mediapoints.nl/api/products?schema=blank']);

        $output->writeln("Done!");
    }
}