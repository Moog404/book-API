<?php

namespace App\Controller;

use App\Dto\BookInput;
use App\Dto\BookOutput;
use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */
class BookController extends AbstractController
{
    /**
     * @Route("/books", name="list_books_with_categories", methods={"GET"})
     */
    public function index(BookRepository $bookRepository, SerializerInterface $serializer)
    {
        $books=$bookRepository->findAll();
        $allBooks=[];
        foreach($books as $book) {
            array_push($allBooks, new BookOutput($book));
        }
        $data= json_encode($allBooks);
        //$data=$serializer->serialize($allBooks, "json");
        return new Response($data, 200, ['Content-Type'=>'application/json']);
    }

    /**
     * @Route("/books/{id}", name="show_book", methods={"GET"})
     */
    public function show(Book $book, BookRepository $bookRepository, SerializerInterface $serializer)
    {
        $bookFind = new BookOutput($book);
        $data= json_encode($bookFind);
        return new Response($data, 200, ['Content-Type'=>'application/json']);
    }

    /**
     * @Route("/books", name="new_book", methods={"POST"})
     */
    public function new(EntityManagerInterface $manager, Request $request, SerializerInterface $serializer, CategoryRepository $categoryRepository)
    {
        $newBook = new Book();
        $bookInput = $serializer->deserialize($request->getContent(),BookInput::class,'json');
        $bookInput->createBookToPersist($newBook, $categoryRepository);
        $manager->persist($newBook);

        $manager->flush();
        $data=['status'=> 201,'message'=> "le livre a bien été ajouté"];
        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/books/{id}", name="update_book", methods={"PUT"})
     */
    public function update(Book $book, Request $request, EntityManagerInterface $manager, CategoryRepository $categoryRepository, BookRepository $bookRepository, SerializerInterface $serializer)
    {
        $bookInput = $serializer->deserialize($request->getContent(),BookInput::class,'json');
        $bookInput->createBookToPersist($book, $categoryRepository);

        $manager->flush();
        $data=['status'=> 201, 'message'=> "le livre a bien été modifié"];
        return new JsonResponse($data, 201);
    }
    /**
     * @Route("/books/{id}", name="delete_book", methods={"DELETE"})
     */
    public function delete(Book $book, EntityManagerInterface $em)
    {
        $em->remove($book);
        $em->flush();
        return new Response("le livre a bien été supprimé", 204);
    }




}
