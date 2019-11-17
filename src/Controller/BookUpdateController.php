<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */

class BookUpdateController extends AbstractController
{
    /**
     * @Route("/booksupdate/{id}", name="update_books", methods={"PUT"} )
     */

    public function update(Book $book, EntityManagerInterface $manager, CategoryRepository $CategoryRepository, ValidatorInterface $validator, BookRepository $BookRepository)
    {
        $request='{"title": "je suis un livre qui a été modifié", "categories": [68, 69, 71]}';
        $bookUpdate = $BookRepository->find($book->getId()); // on récupère le livre correspondant à l'id
        $data = json_decode($request); // on transforme la requête utilisateur pour correspondre aux données du livre
        foreach ($data as $key => $value) {
            if ($key && !empty($value)) {
                if ($key == 'categories') {
                    $bookUpdate->clearCategories();
                    foreach ($data->categories as $item) {
                        dump($CategoryRepository->findOneToDTO($item));
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

}


