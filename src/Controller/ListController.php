<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PlaylistRepository;
use App\Repository\PlaylistSubscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ListController extends AbstractController
{
    #[Route('/lists', name: 'lists')]
    #[IsGranted('ROLE_USER')]
    public function lists(
        PlaylistRepository $playlistRepository,
        PlaylistSubscriptionRepository $playlistSubscriptionRepository
    ): Response
    {
        $playlists = $playlistRepository->findAll();
        $subscriptions = $playlistSubscriptionRepository->findAll();

        return $this->render('lists.html.twig', [
            'playlists' => $playlists,
            'subscriptions' => $subscriptions,
        ]);
    }
}

