<?php

namespace App\Controller;

use App\Repository\CameraGroupRepository;
use App\Repository\TwitchGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditTwitchGroup extends AbstractController
{
    public function __construct(TwitchGroupRepository $twitchGroupRepository, ManagerRegistry $doctrine)
    {
        $this->twitchGroupRepository = $twitchGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke(Request $request, $twitchId, $overlayId): Response
    {
        $data = json_decode($request->getContent(), true);
        $twitchGroup = $this->twitchGroupRepository->findOneBy(['twitchId' => $twitchId, 'overlayId' => $overlayId]);
        // Edit the twitch group
        $twitchGroup->setVisible($data['visible']);

        $this->doctrine->getManager()->persist($twitchGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 201,
            "data" => $twitchGroup
        ]);
    }
}
