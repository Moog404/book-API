<?php

namespace App\Tests\Dto;

use App\Dto\BookInput;
use App\Entity\Book;
use APp\Entity\Category;
use App\Repository\CategoryRepository;
use PHPUnit\Framework\TestCase;

class BookInputTest extends TestCase
{
    public function testConstruct()
    {
        $book = new BookInput();
        $this->assertSame([], $book->getCategories());
    }

    public function testSetTitleOfBookInstance()
    {
        $bookInput=new BookInput();
        $bookInput->setTitle("je suis le titre d'un livre");

        $book=new Book();
        $bookInput->setBookInstanceTitle($book);
        $this->assertEquals("je suis le titre d'un livre", $book->getTitle());
    }

    public function testSetCategoryOfBookInstance()
    {
        $category=new Category();
        $category->setId(4);
        $category->setName("categorie 4");

        /*$category5=new Category();
        $category5->setId(5);
        $category5->setName("categorie 5");
        $category = [$category4, $category5];*/

        $categoryRepository= $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->any())
            ->method('find')
            ->willReturn($category);

        $bookInput=new BookInput();
        $bookInput->setCategories([4]);

        $book=new Book();
        $CategoriesOfBook=$book->getCategories();
        $this->assertEquals(0, count($CategoriesOfBook));

        $bookInput->setBookInstanceCategory($book, $categoryRepository);
        $this->assertEquals(1, count($CategoriesOfBook));

        $this->assertEquals(4, $CategoriesOfBook[0]->getId());
        $this->assertEquals("categorie 4", $CategoriesOfBook[0]->getName());
    }
}
