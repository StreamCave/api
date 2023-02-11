<?php

namespace App\Controller;

use App\Repository\CameraGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CameraGroupController extends AbstractController
{
    public function __construct(CameraGroupRepository $cameraGroupRepository)
    {
        $this->cameraGroupRepository = $cameraGroupRepository;
    }

    public function __invoke($uuid): Response
    {
        $user = $this->cameraGroupRepository->findOneBy(["uuid" => $uuid]);
        return $this->json([
            "statusCode" => 200,
            "data" => $user
        ]);
    }
}
