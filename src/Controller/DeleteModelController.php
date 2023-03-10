<?php

namespace App\Controller;

use App\Repository\MapGroupRepository;
use App\Repository\ModelRepository;
use App\Repository\OverlayRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteModelController extends AbstractController
{
    public function __construct(ModelRepository $modelRepository, ManagerRegistry $doctrine)
    {
        $this->modelRepository = $modelRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {

        $model = $this->modelRepository->findOneBy(['uuid' => $uuid]);
        $overlays = $model->getOverlays();
        foreach ($overlays as $overlay) {
            $overlay->setModel(null);
            $this->doctrine->getManager()->persist($overlay);
        }
        $this->doctrine->getManager()->remove($model);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 202,
            "message" => "Model deleted",
        ]);
    }
}
