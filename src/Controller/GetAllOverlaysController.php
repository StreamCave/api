<?php

namespace App\Controller;

use App\Repository\OverlayRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetAllOverlaysController extends AbstractController
{
    public function __construct(OverlayRepository $overlayRepository, UserRepository $userRepository)
    {
        $this->overlayRepository = $overlayRepository;
        $this->userRepository = $userRepository;
    }

    public function __invoke(Request $request, $uuid): Response
    {
        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);
        $overlays = $this->overlayRepository->findAllAccess($user->getUuid());

        return $this->json([
            "statusCode" => 200,
            "data" => $overlays
        ]);
    }
}
