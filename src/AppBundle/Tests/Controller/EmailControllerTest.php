<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmailControllerTest extends WebTestCase
{
    public function testOpen()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/email/open/{uuid}');
    }

}
