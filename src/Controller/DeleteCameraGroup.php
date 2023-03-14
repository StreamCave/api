<?php

namespace App\Controller;

use App\Repository\CameraGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteCameraGroup extends AbstractController
{
    public function __construct(CameraGroupRepository $cameraGroupRepository, ManagerRegistry $doctrine)
    {
        $this->cameraGroupRepository = $cameraGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {
        $cameraGroup = $this->cameraGroupRepository->findOneBy(['uuid' => $uuid]);
        $widgets = $cameraGroup->getWidgets();
        foreach ($widgets as $widget) {
            $widget->removeCameraGroup($cameraGroup);
            $this->doctrine->getManager()->persist($widget);
        }

        $this->doctrine->getManager()->remove($cameraGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 202,
            "message" => "CameraGroup deleted",
        ]);
    }
}
