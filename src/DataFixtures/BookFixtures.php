<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class BookFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $data=["nouvelle","manga","science","journal"];

        for ($i = 1; $i <= 5; $i++) {
            $book = new Book();
            $book->setTitle("je suis le bouquin n°" . $i);
            $book->addCategory($this->getReference($data[rand(0,3)]));
            $manager->persist($book);
        }

        $book = new Book();
        $book->setTitle("je suis un bouquin avec 2 cat");
        $book->addCategory($this->getReference("nouvelle"));
        $book->addCategory($this->getReference("science"));
        $manager->persist($book);
        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            CategoryFixtures::class
        );
    }
}

