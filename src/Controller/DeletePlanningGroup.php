<?php

namespace App\Controller;

use App\Repository\PlanningGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeletePlanningGroup extends AbstractController
{
    public function __construct(PlanningGroupRepository $planningGroupRepository, ManagerRegistry $doctrine)
    {
        $this->planningGroupRepository = $planningGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {
        $planningGroup = $this->planningGroupRepository->findOneBy(['uuid' => $uuid]);
        $widgets = $planningGroup->getWidgets();
        foreach ($widgets as $widget) {
            $widget->removePlanningGroup($planningGroup);
            $this->doctrine->getManager()->persist($widget);
        }

        $this->doctrine->getManager()->remove($planningGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 202,
            "message" => "PlanningGroup deleted",
        ]);
    }
}
