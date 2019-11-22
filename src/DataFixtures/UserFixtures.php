<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder=$encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('user');
        $user->setPassword($this->encoder->encodePassword($user,'user'));
        $user->setRoles($user->getRoles());
        $manager->persist($user);

        $userAdmin = new User();
        $userAdmin->setUsername("admin");
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setPassword($this->encoder->encodePassword($userAdmin, "admin"));
        $manager->persist($userAdmin);
        $manager->flush();
    }

}

