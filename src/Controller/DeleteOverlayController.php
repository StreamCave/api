<?php

namespace App\Controller;

use App\Repository\OverlayRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteOverlayController extends AbstractController
{
    public function __construct(OverlayRepository $overlayRepository, ManagerRegistry $doctrine)
    {
        $this->overlayRepository = $overlayRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {

        $overlay = $this->overlayRepository->findOneBy(['uuid' => $uuid]);
        foreach ($overlay->getWidgets() as $widget) {
            $this->doctrine->getManager()->remove($widget);
        }
        $this->doctrine->getManager()->remove($overlay);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 202,
            "message" => "Overlay deleted",
        ]);
    }
}
