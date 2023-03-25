<?php

namespace App\Controller;

use App\Repository\OverlayRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GetAllOverlaysController extends AbstractController
{
    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, OverlayRepository $overlayRepository, UserRepository $userRepository)
    {
        $this->overlayRepository = $overlayRepository;
        $this->userRepository = $userRepository;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->jwtManager = $jwtManager;
    }

    public function __invoke(Request $request, $uuid): Response
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $user = $this->userRepository->findOneBy(['uuid' => $decodedJwtToken['uuid']]);
        $overlays = $this->overlayRepository->findAllAccess($user->getUuid());

        return $this->json([
            "statusCode" => 200,
            "data" => $overlays
        ]);
    }
}
