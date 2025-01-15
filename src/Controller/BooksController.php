<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BooksController extends AbstractController
{
    private const AUTH_TOKEN = 'token123456';

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

    #[Route('/api/books', name: 'create_book', methods: ['POST'])]
    public function createBook(
        Request $request,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (empty($data['title']) || empty($data['author']) || empty($data['isbn'])
            || empty($data['publication_year'])) {
            return new JsonResponse([
                'error' => 'All fields are required',
            ], Response::HTTP_BAD_REQUEST);
        }
        if ($data['publication_year'] > intval(date('Y'))
        || $data['publication_year'] < 1900) {
            return new JsonResponse([
                'error' => 'Publication year must be between 1900 and current year',
            ], Response::HTTP_BAD_REQUEST);
        }
        if (13 != strlen($data['isbn'])) {
            return new JsonResponse([
                'error' => 'ISBN must be exactly 13 characters',
            ], Response::HTTP_BAD_REQUEST);
        }
        $header = $request->headers->get('Authorization');
        if ('Bearer '.self::AUTH_TOKEN != $header) {
            return new JsonResponse([
                'error' => 'Not authorized',
            ], Response::HTTP_BAD_REQUEST);
        }
        $book = new Book();
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setIsbn($data['isbn']);
        $book->setPublicationYear($data['publication_year']);
        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'isbn' => $book->getIsbn(),
            'publication_year' => $book->getPublicationYear(),
        ], Response::HTTP_CREATED);
    }
}
