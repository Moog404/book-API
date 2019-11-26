<?php

namespace App\Controller;

use App\Dto\BookInput;
use App\Dto\BookOutput;
use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api")
 */
class BookController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/books", name="list_books", methods={"GET"})
     * @SWG\Get(
     *     summary="accéder à tous les livres",
     *     description="récupère tous les livres")
     * @SWG\Response(
     *     response=200,
     *     description="Retourne les livres",
     *     @SWG\schema(
     *          @SWG\Property(property="id", type="string"),
     *          @SWG\Property(property="title", type="string"),
     *          @SWG\Property(property="categories", type="object",
     *              @SWG\Property(property="id", type="integer"),
     *              @SWG\Property(property="name", type="string"),
     *              @SWG\Property(property="subCategory", type="array",
     *                  @SWG\Items(type="string"))
     *          )
     *     )
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="integer",
     *     description="pagination des livres (10 livres par page)"
     * )
     *
     * @SWG\Tag(name="Book")
     * @Security(name="Bearer")
     */
    public function index(BookRepository $bookRepository)
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
     * @SWG\Tag(name="Book")
     * @SWG\Get(
     *     summary="récupère les ressources d'un livre ")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Retourne un livre suivant l'id entrée",
     *     @SWG\schema(
     *          @SWG\Property(property="id", type="integer"),
     *          @SWG\Property(property="title", type="string"),
     *          @SWG\Property(property="category", type="array",
     *              @SWG\Items(type="integer"))
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="pas de ressource trouvée"
     * )
     * @Security(name="Bearer")
     */
    public function show(Book $book)
    {
        $bookFind = new BookOutput($book);
        $data= json_encode($bookFind);
        return new Response($data, 200, ['Content-Type'=>'application/json']);
    }

    /**
     * @Route("/books", name="new_book", methods={"POST"})
     * @SWG\Tag(name="Book")
     * @SWG\Post(summary="Ajout d'un livre")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="title* / category peut ne pas être défini, un tableau vide ou un tableau avec les id de catégories existantes",
     *     @SWG\Schema(
     *          required={"title"},
     *          @SWG\Property(property="title", type="string"),
     *          @SWG\Property(property="category", type="array",
     *              @SWG\Items(type="integer"))
     *     )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="ajout d'un nouveau livre",
     *     @SWG\schema(
     *          required={"title"},
     *          @SWG\Property(property="title", type="string"),
     *          @SWG\Property(property="category", type="array",
     *              @SWG\Items(type="integer"))
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="entrée non valide"
     * )
     *  @SWG\Response(
     *     response=401,
     *     description="Vous devez vous connecter pour pourvoir poster un nouveau livre"
     * )
     * @Security(name="Bearer")
     */
    public function new(EntityManagerInterface $manager, Request $request, SerializerInterface $serializer, CategoryRepository $categoryRepository)
    {
        $newBook = new Book();
        $bookToAdd = $serializer->deserialize($request->getContent(),BookInput::class,'json');
        $bookToAdd->setBookInstanceTitle($newBook);
        $bookToAdd->setBookInstanceCategory($newBook, $categoryRepository);
        $manager->persist($newBook);
        $manager->flush();
        $data=['status'=> 201,'message'=> "le livre a bien été ajouté"];
        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/books/{id}", name="update_book", methods={"PUT"})
     * @SWG\Tag(name="Book")
     * @SWG\Put(
     *     summary="mis à jour d'un livre")
     * @SWG\Response(
     *     response=200,
     *     description="Le livre a bien été modifié",
     *     @SWG\schema(
     *          @SWG\Property(property="title", type="string"),
     *          @SWG\Property(property="category", type="array",
     *              @SWG\Items(type="integer"))
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="entrée invalide, le livre ne peut pas être modifié"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="la ressource n'a pas été trouvée ou inexistante"
     * )
     * @Security(name="Bearer")
     */
    public function update(Book $book, Request $request, CategoryRepository $categoryRepository, SerializerInterface $serializer)
    {
        $bookToUpdate = $serializer->deserialize($request->getContent(),BookInput::class,'json');
        $bookToUpdate->setBookInstanceTitle($book);
        $bookToUpdate->setBookInstanceCategory($book, $categoryRepository);
        $this->em->flush();
        $data=['status'=> 201, 'message'=> "le livre a bien été modifié"];
        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/books/{id}", name="delete_book", methods={"DELETE"})
     * @SWG\Tag(name="Book")
     * @SWG\Delete(summary="suppression d'un livre")
     * @SWG\Response(
     *     response=204,
     *     description="le livre a bien été supprimé"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="la ressource n'a pas été trouvée ou inexistante"
     * )
     */
    public function delete(Book $book)
    {
        $this->em->remove($book);
        $this->em->flush();
        return new Response("le livre a bien été supprimé", 204);
    }
}
