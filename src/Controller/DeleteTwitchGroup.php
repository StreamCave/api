<?php

namespace App\Controller;

use App\Repository\TwitchGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteTwitchGroup extends AbstractController
{
    public function __construct(TwitchGroupRepository $twitchGroupRepository, ManagerRegistry $doctrine)
    {
        $this->twitchGroupRepository = $twitchGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {
        $twitchGroup = $this->twitchGroupRepository->findOneBy(['uuid' => $uuid]);
        $widgets = $twitchGroup->getWidgets();
        foreach ($widgets as $widget) {
            $widget->setTwitchGroup(null);
            $this->doctrine->getManager()->persist($widget);
        }

        $this->doctrine->getManager()->remove($twitchGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 200,
            "message" => "TwitchGroup deleted",
        ]);
    }
}
