<?php

namespace App\Controller;

use App\Repository\OverlayRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

// class OverlaysOwn
// {
//     private $overlays;

//     public function __construct(array $overlays)
//     {
//         $this->overlays = $overlays;
//     }

//     public function getOverlays(): array
//     {
//         return $this->overlays;
//     }
// }

// class OverlaysAccess
// {
//     private $overlaysAccess;

//     public function __construct(array $overlaysAccess)
//     {
//         $this->overlaysAccess = $overlaysAccess;
//     }

//     public function getOverlaysAccess(): array
//     {
//         return $this->overlaysAccess;
//     }
// }

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
        $overlaysAccess = $this->overlayRepository->findAllAccess($user->getUuid());
        $overlayOwner = $this->overlayRepository->findOneById($user->getId());

        // Afficher que les id et nom des overlays qui ont été partagés avec l'utilisateur
        $overlaysAccess = array_map(function($overlay) {
            $userAccess = $overlay->getUserAccess()->getValues();
            $userAccess = array_map(function($user) {
                return [
                    'uuid' => $user->getUuid(),
                ];
            }, $userAccess);
            $model = array($overlay->getModel());
            $model = array_map(function($model) {
                return [
                    'uuid' => $model->getUuid(),
                    'tags' => $model->getTags(),
                ];
            }, $model);
            return [
                'uuid' => $overlay->getUuid(),
                'name' => $overlay->getName(),
                'image' => $overlay->getImage(),
                'listened' => $overlay->isListened(),
                'Model' => $model,
                'userAccess' => $userAccess,
            ];
        }, $overlaysAccess);

        $overlayOwner = array_map(function($overlay) {
            // Object to array
            $userAccess = $overlay->getUserAccess()->getValues();
            $userAccess = array_map(function($user) {
                return [
                    'uuid' => $user->getUuid(),
                ];
            }, $userAccess);
            $model = array($overlay->getModel());
            $model = array_map(function($model) {
                return [
                    'uuid' => $model->getUuid(),
                    'tags' => $model->getTags(),
                ];
            }, $model);
            return [
                'uuid' => $overlay->getUuid(),
                'name' => $overlay->getName(),
                'image' => $overlay->getImage(),
                'Model' => $model,
                'userAccess' => $userAccess,
            ];
        }, $overlayOwner);
        $overlays = array(
            'overlays' => $overlayOwner,
            'overlaysAccess' => $overlaysAccess
        );
    
    return $this->json([
            "statusCode" => 200,
            "data" => $overlays
        ]);
    }
}
