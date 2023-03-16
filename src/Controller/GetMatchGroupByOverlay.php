<?php

namespace App\Controller;

use App\Repository\CameraGroupRepository;
use App\Repository\MatchGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetMatchGroupByOverlay extends AbstractController
{
    public function __construct(MatchGroupRepository $matchGroupRepository, ManagerRegistry $doctrine)
    {
        $this->matchGroupRepository = $matchGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke(Request $request, $overlayuuid): Response
    {
        $cameraGroupS = $this->matchGroupRepository->findBy(['overlayId' => $overlayuuid]);

        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 200,
            "data" => $cameraGroupS
        ]);
    }
}
