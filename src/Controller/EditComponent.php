<?php

namespace App\Controller;

use App\Repository\CameraGroupRepository;
use App\Repository\WidgetRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditComponent extends AbstractController
{
    public function __construct(WidgetRepository $widgetRepository, CameraGroupRepository $cameraGroupRepository, ManagerRegistry $doctrine)
    {
        $this->widgetRepository = $widgetRepository;
        $this->cameraGroupRepository = $cameraGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        foreach ($data as $key => $widget) {
            if ($widget["styles"] == null) {
                $widget["styles"] = [];
            }
            if ($widget["name"] == "cameras") {
                // On actualise le style dans cameraGroup
                $cameraGroup = $this->cameraGroupRepository->findOneBy(["uuid" => $widget["uuid"]]);
                $cameraGroup->setStyles($widget["styles"]);
                $this->doctrine->getManager()->persist($cameraGroup);
                $this->doctrine->getManager()->flush();
            } else {
                // On actualise le style dans widget directement
                $widgetDb = $this->widgetRepository->findOneBy(["uuid" => $widget["uuid"]]);
                $widgetDb->setStyles($widget["styles"]);
                $this->doctrine->getManager()->persist($widgetDb);
                $this->doctrine->getManager()->flush();
            }
        }

        return $this->json([
            "statusCode" => 201,
            "message" => "Styles updated"
        ]);
    }
}
