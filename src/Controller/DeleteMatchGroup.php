<?php

namespace App\Controller;

use App\Repository\MatchGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteMatchGroup extends AbstractController
{
    public function __construct(MatchGroupRepository $matchGroupRepository, ManagerRegistry $doctrine)
    {
        $this->matchGroupRepository = $matchGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {
        $matchGroup = $this->matchGroupRepository->findOneBy(['uuid' => $uuid]);
        $widgets = $matchGroup->getWidgets();
        foreach ($widgets as $widget) {
            $widget->setMatchGroup(null);
            $this->doctrine->getManager()->persist($widget);
        }

        $this->doctrine->getManager()->remove($matchGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 202,
            "message" => "InfoGroup deleted",
        ]);
    }
}
