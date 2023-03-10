<?php

namespace App\Controller;

use App\Repository\MapGroupRepository;
use App\Repository\OverlayRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteUserController extends AbstractController
{
    public function __construct(UserRepository $userRepository, OverlayRepository $overlayRepository, ManagerRegistry $doctrine)
    {
        $this->userRepository = $userRepository;
        $this->overlayRepository = $overlayRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);
        $overlays = $this->overlayRepository->findBy(['userOwner' => $user]);
        foreach ($overlays as $overlay) {
            foreach ($overlay->getWidgets() as $widget) {
                $this->doctrine->getManager()->remove($widget);
            }
            $this->doctrine->getManager()->remove($overlay);
        }

        $this->doctrine->getManager()->remove($user);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 202,
            "message" => "User deleted",
        ]);
    }
}
