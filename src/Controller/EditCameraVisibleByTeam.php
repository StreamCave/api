<?php

namespace App\Controller;

use App\Entity\CameraGroup;
use App\Entity\InfoGroup;
use App\Entity\MatchGroup;
use App\Entity\Model;
use App\Entity\Overlay;
use App\Entity\PollGroup;
use App\Entity\PopupGroup;
use App\Entity\TweetGroup;
use App\Entity\Widget;
use App\Repository\CameraGroupRepository;
use App\Repository\LibWidgetRepository;
use App\Repository\OverlayRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

class EditCameraVisibleByTeam extends AbstractController
{
    public function __construct(CameraGroupRepository $cameraGroupRepository, ManagerRegistry $doctrine)
    {
        $this->cameraGroupRepository = $cameraGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke(Request $request, $team, $overlayId): Response
    {
        $data = json_decode($request->getContent(), true);
        $cameraGroupS = $this->cameraGroupRepository->findAllByTeamAndOverlay($team, $overlayId);
        foreach ($cameraGroupS as $cameraGroup) {
            $cameraGroup->setVisible($data['visible']);
            $this->doctrine->getManager()->persist($cameraGroup);
        }

        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 201,
            "data" => $data
        ]);
    }
}
