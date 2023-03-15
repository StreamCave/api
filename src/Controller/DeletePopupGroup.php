<?php

namespace App\Controller;

use App\Repository\PopupGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeletePopupGroup extends AbstractController
{
    public function __construct(PopupGroupRepository $popupGroupRepository, ManagerRegistry $doctrine)
    {
        $this->popupGroupRepository = $popupGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {
        $popupGroup = $this->popupGroupRepository->findOneBy(['uuid' => $uuid]);
        $widgets = $popupGroup->getWidgets();
        foreach ($widgets as $widget) {
            $widget->setPopupGroup(null);
            $this->doctrine->getManager()->persist($widget);
        }

        $this->doctrine->getManager()->remove($popupGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 200,
            "message" => "PopupGroup deleted",
        ]);
    }
}
