<?php

namespace App\Controller;

use App\Repository\OverlayRepository;
use App\Repository\WidgetRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteWidgetController extends AbstractController
{
    public function __construct(WidgetRepository $widgetRepository, ManagerRegistry $doctrine)
    {
        $this->widgetRepository = $widgetRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {

        $widget = $this->widgetRepository->findOneBy(['uuid' => $uuid]);
        $this->doctrine->getManager()->remove($widget);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 200,
            "message" => "Widget deleted",
        ]);
    }
}
