<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Category;
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
class CategoryController extends AbstractController
{
    /**
     * @Route("/categories/{id}", name="show_category", methods={"GET"})
     */
    public function show(Category $category, CategoryRepository $categoryRepository, SerializerInterface $serializer)
    {
        $categoryFind = $categoryRepository->find($category->getId());
        dump($categoryFind->getBooks()->getValues());
        $data = $serializer->serialize($categoryFind, "json", ["groups"=>["category:read"]]);
        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/categories", name="list_categories", methods={"GET"})
     */
    public function index(CategoryRepository $CategoryRepository, SerializerInterface $serializer)
    {
        $categories = $CategoryRepository->findAll();
        $data = $serializer->serialize($categories, "json", ["groups"=>["category:read"]]);

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/categories", name="new_category", methods={"POST"})
     */
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');
        $em->persist($category);
        $em->flush();
        $data = [
            'status' => 201,
            'message' => "la catégorie a bien été ajouté"
        ];
        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/categories/{id}", name="update_category", methods={"PUT"})
     */
    public function update(Request $request, Category $category, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $categoryUpdate = $em->getRepository(Category::class)->find($category->getId());
        $data = json_decode($request->getContent());
        foreach ($data as $key => $value) {
            if ($key && !empty($value)) {
                $setter = 'set' . ucfirst($key); // ex: setTitle
                $categoryUpdate->$setter($value);
            }
        }
        $errors = $validator->validate($categoryUpdate);
        if (count($errors)) {
            $errors = $serializer->serialize($errors, "json");
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
        $em->flush();
        $data = [
            'status' => 201,
            'message' => "la catégorie a bien été modifié"
        ];
        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/categories/{id}", name="delete_category", methods={"DELETE"})
     */
    public function delete(Category $category, EntityManagerInterface $em)
    {
        $em->remove($category);
        $em->flush();
        return new Response(null, 204);  // faire une redirection
    }
}
