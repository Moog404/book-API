<?php

namespace App\Controller;

use App\Dto\BookInput;
use App\Dto\BookOutput;
use App\Entity\Book;
use App\Entity\Category;
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
        //$bookFind = $bookRepository->find($book->getId());
        //$encoders = [new JsonEncoder()];
        //$normalizers = [new ObjectNormalizer()];
        //$serializer = new Serializer($normalizers, $encoders);
        $bookFind = $bookRepository->findOneToDTO($book->getId());
        $data= json_encode($bookFind);
        return new Response($data, 200, ['Content-Type'=>'application/json']);
    }

    /**
     * @Route("/books", name="new_book", methods={"POST"})
     */
    public function new(Request $request, EntityManagerInterface $manager, CategoryRepository $CategoryRepository)
    {
        $book=json_decode($request->getContent());
        $newBook= new BookInput();
        $newBook->setTitle($book->title);
        if(property_exists($book, 'categories')){
            foreach($book->categories as $item){
                $newBook->addCategory($CategoryRepository->findOneToDTO($item));
            }
        }
        $manager->persist($newBook);
        $manager->flush();
        $data=[
            'status'=> 201,
            'message'=> "le livre a bien été ajouté"
        ];
        return new JsonResponse($data, 201);
    }
    /**
     * @Route("/books/{id}", name="update_book", methods={"PUT"})
     */
    public function update(Request $request, Book $book, EntityManagerInterface $manager, CategoryRepository $CategoryRepository, ValidatorInterface $validator, BookRepository$BookRepository)
    {
        $bookUpdate = $BookRepository->find($book->getId()); // on récupère le livre correspondant à l'id
        $data = json_decode($request->getContent()); // on transforme la requête utilisateur pour correspondre aux données du livre
        foreach ($data as $key => $value)
        {
            if ($key && !empty($value)) {
                if ($key == 'categories') {
                    $bookUpdate->clearCategories();
                    foreach ($data->categories as $item) {
                        $bookUpdate->addCategory($CategoryRepository->findOneToDTO($item));
                    }
                }else{
                    $setter = 'set' . ucfirst($key);
                    $bookUpdate->$setter($value);
                }
            }
        }
        $manager->flush();
        $data=[
            'status'=> 201,
            'message'=> "le livre a bien été modifié"
        ];
        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/books/{id}", name="delete_book", methods={"DELETE"})
     */
    public function delete(Book $book, EntityManagerInterface $em)
    {
        $em->remove($book);
        $em->flush();
        return new Response(null, 204);
    }

}
