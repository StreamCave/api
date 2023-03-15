<?php

namespace App\Controller;

use App\Repository\InfoGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteInfoGroup extends AbstractController
{
    public function __construct(InfoGroupRepository $infoGroupRepository, ManagerRegistry $doctrine)
    {
        $this->infoGroupRepository = $infoGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {
        $infoGroup = $this->infoGroupRepository->findOneBy(['uuid' => $uuid]);
        $widgets = $infoGroup->getWidgets();
        foreach ($widgets as $widget) {
            $widget->setInfoGroup(null);
            $this->doctrine->getManager()->persist($widget);
        }

        $this->doctrine->getManager()->remove($infoGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 200,
            "message" => "InfoGroup deleted",
        ]);
    }
}
