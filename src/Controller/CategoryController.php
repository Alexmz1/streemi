<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MediaRepository;
use App\Repository\CategoryRepository;

class CategoryController extends AbstractController
{
    #[Route('/discover', name: 'movie_discover')]
    public function discover_category(MediaRepository $mediaRepository, CategoryRepository $categoryRepository): Response
    {
        $medias = $mediaRepository->findByTrending(3);
        $categories = $categoryRepository->findAll();

        return $this->render('discover.html.twig', [
            'medias' => $medias,
            'categories' => $categories
        ]);
    }

    #[Route('/category/{id}', name: 'category_detail')]
    public function category_detail(Category $category, MediaRepository $mediaRepository): Response
    {
        return $this->render('category.html.twig', [
            'category' => $category,
            'trending' => $mediaRepository->findByTrending(3)
        ]);
    }
}