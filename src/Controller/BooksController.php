<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class BooksController extends AbstractController
{
    #[Route('/', name: 'app_books')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/BooksController.php',
        ]);
    }
    #[Route('/api/books', name: 'get_books', methods: ['GET'])]
    public function getBooks(
        Request $request,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $author = $request->get('author');
        $year = $request->get('year');
        $query = $entityManager->getRepository(Book::class)->createQueryBuilder('b');
        if ($author) {
            $query->andWhere('b.author = :author')->setParameter('author', $author);
        }
        if ($year) {
            $query->andWhere('b.publication_year = :year')->setParameter('year', $year);
        }
        $books = $query->getQuery()->getResult();
        return $this->json($books);
    }
}
