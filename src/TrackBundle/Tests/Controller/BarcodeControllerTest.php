<?php

namespace TrackBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BarcodeControllerTest extends WebTestCase
{
    public function testPrint()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/printBarcode');
    }

}
