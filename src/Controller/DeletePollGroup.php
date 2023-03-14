<?php

namespace App\Controller;

use App\Repository\PollGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeletePollGroup extends AbstractController
{
    public function __construct(PollGroupRepository $pollGroupRepository, ManagerRegistry $doctrine)
    {
        $this->pollGroupRepository = $pollGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {
        $pollGroup = $this->pollGroupRepository->findOneBy(['uuid' => $uuid]);
        $widgets = $pollGroup->getWidgets();
        foreach ($widgets as $widget) {
            $widget->setPollGroup(null);
            $this->doctrine->getManager()->persist($widget);
        }

        $this->doctrine->getManager()->remove($pollGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 202,
            "message" => "PollGroup deleted",
        ]);
    }
}
