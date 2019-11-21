<?php

namespace App\Dto;


use App\Entity\Book;
use App\Entity\Category;
use App\Repository\CategoryRepository;

class BookInput
{
    public $title;
    public $categories;

    public function __construct()
    {
        $this->categories=[];
    }

   public function createFromBook(Book $book)
    {
        $this->title= $book->getTitle();
        $this->categories = array_map(function (Category $category){
            return $category->getId();
        },$this->getCategories());
        return $this;
    }

    public function setBookInstanceTitle(Book $book)
    {
        $book->setTitle($this->getTitle());
    }

    public function setBookInstanceCategory(Book $book, CategoryRepository $categoryRepository)
    {
        foreach($this->getCategories() as $id) {
            $book->addCategory($categoryRepository->find($id));
        }
    }

    public function updateBook(Book $book, CategoryRepository $categoryRepository){
        $book->setTitle($this->getTitle());
        foreach($this->getCategories() as $id) {
            $book->addCategory($categoryRepository->find($id));
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function getCategories(): ?array
    {
        return $this->categories;
    }

    public function addCategory(int $category): self
    {
        if (in_array($category,$this->categories)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategories(int $category): self
    {
        if (in_array($category,$this->categories)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function setCategories(array $categories)
    {
        $this->categories = $categories;
    }

}

