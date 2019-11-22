<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class SecurityController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $userRegister = json_decode($request->getContent());

        if(isset($userRegister->username) && isset($userRegister->password)){
            $user = new User();
            $user->setUsername($userRegister->username)
                ->setPassword($passwordEncoder->encodePassword($user, $userRegister->password))
                ->setRoles($user->getRoles());

            $errors = $validator->validate($user);
            if(count($errors)) {
                $errors = $serializer->serialize($errors, 'json');
                return new Response($errors, 500, [
                    'Content-Type' => 'application/json'
                ]);
            }

            $em->persist($user);
            $em->flush();

            $data = [
                'status' => 201,
                'message' => 'L\'utilisateur a été créé'
            ];

            return new JsonResponse($data, 201);
        }

        $data = [
            'status' => 500,
            'message' => "Vous devez renseigner l'username et le password"
        ];

        return new JsonResponse($data, 500);
    }

    /**
     * @Route("api/user", name="user", methods={"GET"})
     */
    public function userConnected()
    {
        $user = $this->security->getUser();
        $data = [
            'username' => $user->getUsername(),
            'roles' => $user->getRoles()
        ];

        return new JsonResponse($data, 201);
    }

}