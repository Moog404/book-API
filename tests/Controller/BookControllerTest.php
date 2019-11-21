<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $client = static::createClient();
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
        $client = static::createClient();

        $client->request('GET', '/api/books/fsdg');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/books/12');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

   public function  testPostNewBook()
   {
        $client = static::createClient();
        $client->request('POST', '/api/books', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"un nouveau livre de test avec POST", "categories":[5]}'
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
        $client = static::createClient();

        $client->request(
            'PUT', '/api/books/12', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"je suis un livre modifié", "categories":[5,6]}'
        );

        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $comment = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $comment);

        $message= $comment["message"];
        $this->assertStringContainsString('le livre a bien été modifié', $message);

        $client->request('GET', '/api/books/12');
        $content =json_decode($client->getResponse()->getContent());

        $this->assertEquals("je suis un livre modifié", $content->title);
        $this->assertEquals(12, $content->id);
    }

    public function testDeleteBook()
    {
        $client =static::createClient();

        $client->request(
            'DELETE', '/api/books/12');
        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());

        $client->request(
            'GET', '/api/books/12');
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

    }

}