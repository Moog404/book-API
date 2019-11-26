<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    /**
     * Create a client with a default Authorization header
     *
     * @param string $username
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function createAuthenticatedClient($username = 'admin', $password = 'admin')
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/login_check',
            [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $username,
                'password' => $password,
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    public function testIndexNoAuthenticatedClient()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/books');

        $response=$client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }


    public function testIndexAuthenticatedClient()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/books');

        $response=$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertStringContainsString('title', $response->getContent()); //assertContains déprécié
        $this->assertStringContainsString('categories', $response->getContent());
        $content= json_decode($response->getContent(), true);

        $book=$content[0];
        $this->assertArrayHasKey('id', $book);
        $this->assertArrayHasKey('title', $book);
        $this->assertArrayHasKey('categories', $book);
    }

    public function testShow()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/books/fsdg');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/books/2');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testPostNewBookNoAuthenticatedClient()
    {
        $client = $this->createClient();

        $client->request('POST', '/api/books', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"un nouveau livre de test avec POST", "categories":[4]}'
        );

        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

   public function testPostNewBook()
   {
       $client = $this->createAuthenticatedClient();

       $client->request('POST', '/api/books', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"un nouveau livre de test avec POST", "categories":[4]}'
        );

        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $comment = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $comment);

        $message= $comment["message"];
        $this->assertStringContainsString('le livre a bien été ajouté', $message);

        $client->request('GET', '/api/books');
        $this->assertStringContainsString('un nouveau livre de test avec POST', $client->getResponse()->getContent());

   }

    public function  testUpdateBook() {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PUT', '/api/books/2', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"je suis un livre modifié", "categories":[2,3]}'
        );

        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $comment = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $comment);

        $message= $comment["message"];
        $this->assertStringContainsString('le livre a bien été modifié', $message);

        $client->request('GET', '/api/books/2');
        $content =json_decode($client->getResponse()->getContent());

        $this->assertEquals("je suis un livre modifié", $content->title);
        $this->assertEquals(2, $content->id);
    }

    public function testDeleteBook()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'DELETE', '/api/books/2');
        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());

        $client->request(
            'GET', '/api/books/2');
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

    }

}