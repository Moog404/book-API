<?php

namespace App\Dto;

use App\Entity\Book;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;

final class BookOutput
{
    public $id;
    public $title;
    public $categories;

    public function __construct(Book $book)
    {
        $this->id=$book->getId();
        $this->title=$book->getTitle();
        $this->categories= $this->getCategoriesFromBook($book);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return array
     */
    public function getCategoriesFromBook(Book $book): array
    {
        $ids= [];
        foreach ($book->getCategories() as $item){
            $ids[] = $item->getId();
        }
        return $ids;
    }

    public function getCategoriesNamesFromBook(Book $book): array
    {
        $names= [];
        foreach ($book->getCategories() as $item){
            $names[] = $item->getName();;
        }

        return $names;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function getCategories(){
        return $this->getCategories();
    }
}


