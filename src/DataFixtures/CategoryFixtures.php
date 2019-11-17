<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
   // private $name = ['roman', 'nouvelle', 'journal', 'manga', 'thriller','psychologie'];

    public function load(ObjectManager $manager)
    {
        /*for($i=0; $i < count($this->name); $i++)
        {
            $category = new Category();
            $category->setName($this->name[$i]);
            $manager->persist($category);
        }
        $manager->flush();*/
        $nouvelle = new Category();
        $nouvelle->setName('Nouvelle');
        $manager->persist($nouvelle);

        $manga = new Category();
        $manga->setName('Manga');
        $manager->persist($manga);

        $science = new Category();
        $science->setName('Science');
        $manager->persist($science);

        $journal = new Category();
        $journal->setName('Journal');
        $manager->persist($journal);

        $manager->flush();

        $this->addReference('nouvelle', $nouvelle);
        $this->addReference('manga', $manga);
        $this->addReference('science', $science);
        $this->addReference('journal', $journal);
    }

}
