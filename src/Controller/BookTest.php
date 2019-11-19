<?php

namespace App\Controller;

use App\Dto\bookOutput;
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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */
class BookTest extends AbstractController
{

    /**
     * @Route("/bookstest", name="list_booktest", methods={"GET"})
     */
    public function index(BookRepository $bookRepository, SerializerInterface $serializer)
    {
        $books=$bookRepository->findAll();
        $data=$serializer->serialize($books, "json", ['groups' => ['books:read']]);
        return new Response($data, 200, ['Content-Type'=>'application/json']);
    }

    /**
     * @Route("/bookstest/{id}/categories"), name="list_category_of_booktest", methods={"GET"})
     */
    public function bookCategories(Book $book, BookRepository $bookRepository, CategoryRepository $categoryRepository, SerializerInterface $serializer)
    {
        $bookFind=$bookRepository->find($book->getId());
        $categoryBook=$bookFind->getCategories()->getValues();
        $data=$serializer->serialize($bookFind, "json", ['groups' => ['books:read']] );
        return new Response($data, 200, ['Content-Type'=>'application/json']);
    }

    /**
     * @Route("/bookstest", name="new_booktest", methods={"POST"})
     */
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager)
    {
        $book=$serializer->deserialize($request->getContent(), Book::class, 'json');
        $manager->persist($book);
        $manager->flush();
        $data=[
            'status'=> 201,
            'message'=> "le livre a bien été ajouté"
        ];
        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/bookstest/{id}", name="update_booktest", methods={"PUT"})
     */
    public function update(Request $request, Book $book, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator)
    {
        $bookUpdate=$manager->getRepository(Book::class)->find($book->getId()); // on récupère le livre correspondant à l'id
        $data=json_decode($request->getContent()); // on transforme la requête utilisateur pour correspondre aux données du livre
        foreach($data as $key => $value){
            if($key && !empty($value)){
                $setter ='set'.ucfirst($key); // ex: setTitle
                $bookUpdate->$setter($value);
            }
        }
        $errors = $validator->validate($bookUpdate);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
        $manager->flush();
        $data=[
            'status'=> 201,
            'message'=> "le livre a bien été modifié"
        ];
        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/bookstest", name="new_booktest2", methods={"POST"})
     */
    public function newTest(Request $request, EntityManagerInterface $manager, CategoryRepository $CategoryRepository)
    {
        $book=json_decode($request->getContent());
        $newBook = new Book();
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
     * @Route("/bookstest/{id}", name="updatetest_booktest", methods={"PUT"})
     */
    public function updateTest(Request $request, Book $book, EntityManagerInterface $manager, CategoryRepository $CategoryRepository, BookRepository$BookRepository)
    {
        $bookUpdate = $BookRepository->find($book->getId()); // instance de Book retrouvé via son Id
        $data = json_decode($request->getContent());
        foreach ($data as $key => $value) {
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
     * @Route("/bookstest/{id}", name="deletetest_book", methods={"DELETE"})
     */
    public function delete(Book $book, EntityManagerInterface $em)
    {
        $em->remove($book);
        $em->flush();
        return new Response("le livre a bien été supprimé", 204);
    }

}
