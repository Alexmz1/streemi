<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Movie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MovieController extends AbstractController
{
    // #[Route('/media/{id}', name: 'movie_detail')]
    // public function index(int $id, ManagerRegistry $doctrine): Response
    // {
    //     $media = $doctrine
    //         ->getRepository(Media::class)
    //         ->find($id);

    //     if (!$media) {
    //         throw $this->createNotFoundException('Media not found');
    //     }

    //     return $this->render('detail.html.twig', [
    //         'media' => $media
    //     ]);
    // }

    #[Route('/media', name: 'movie_detail')]
    public function index(Media $media): Response
    {
        return $this->render('detail.html.twig', [
            'media' => $media
        ]);
    }

}