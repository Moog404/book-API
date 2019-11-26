<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Swagger\Annotations as SWG;




/**
 * @Route("/api")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/categories", name="list_categories", methods={"GET"})
     * @SWG\Get(summary="accèder aux catégories possibles d'un livre")
     * @SWG\Response(
     *     response=200,
     *     description="Retourne les ressources de la catégorie"
     * )
     * @SWG\Tag(name="Category")
     * @Security(name="Bearer")
     */
    public function index(CategoryRepository $CategoryRepository, SerializerInterface $serializer)
    {
        $categories = $CategoryRepository->findAll();
        $data = $serializer->serialize($categories, "json", ["groups"=>["category:read"]]);

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/categories/{id}", name="show_category", methods={"GET"})
     * @SWG\Get(summary="accéder aux ressources d'une catégorie")
     * @SWG\Response(
     *      response=200,
     *      description="Retourne toutes les catégories possible pour un livre",
     *      @SWG\schema(
     *          @SWG\Property(property="id", type="integer"),
     *          @SWG\Property(property="name", type="string"),
     *          @SWG\Property(property="books", type="array",
     *              @SWG\Items(ref=@Model(type=Book::class))
     *          )
     *      )
     * )
     * @SWG\Tag(name="Category")
     * @Security(name="Bearer")
     */
    public function show(Category $category, CategoryRepository $categoryRepository, SerializerInterface $serializer)
    {
        $categoryFind = $categoryRepository->find($category->getId());
        $data = $serializer->serialize($categoryFind, "json", ["groups"=>["category:read"]]);
        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/categories", name="new_category", methods={"POST"})
     * @SWG\Response(
     *     response=400,
     *     description="entrée invalide, la catégorie ne peut pas être ajoutée"
     * )
     * @SWG\Tag(name="Category")
     * @Security(name="Bearer")
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
     * @SWG\Response(
     *     response=404,
     *     description="la ressource à modifier n'a pas été trouvée ou inexistante"
     * )
     * @SWG\Tag(name="Category")
     * @Security(name="Bearer")
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
     *
     * @SWG\Tag(name="Category")
     * @SWG\Response(
     *     response=404,
     *     description="la ressource n'a pas été trouvée ou inexistante"
     * )
     * @Security(name="Bearer")
     *
     * nécessite le rôle d'admin pour supprimer la catégories / impossible en tant que User
     * @IsGranted("ROLE_ADMIN")
     *
     */
    public function delete(Category $category, EntityManagerInterface $em)
    {
        $em->remove($category);
        $em->flush();
        return new Response(null, 204);  // faire une redirection
    }
}
