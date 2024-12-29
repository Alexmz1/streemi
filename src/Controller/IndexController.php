<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\MediaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function __invoke(MediaRepository $mediaRepository): Response
    {
        $medias = $mediaRepository->findAll();

        return $this->render('index.html.twig', [
            'medias' => $medias,
        ]);
    }
}
