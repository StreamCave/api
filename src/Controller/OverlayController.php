<?php

namespace App\Controller;

use App\Repository\OverlayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class OverlayController extends AbstractController
{
    public function __construct(OverlayRepository $overlayRepository)
    {
        $this->overlayRepository = $overlayRepository;
    }

    public function __invoke($uuid): Response
    {
        $user = $this->overlayRepository->findOneBy(["uuid" => $uuid]);
        return $this->json([
            "statusCode" => 200,
            "data" => $user
        ]);
    }
}
