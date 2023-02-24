<?php

namespace App\Controller;

use App\Repository\MapGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteMapGroupController extends AbstractController
{
    public function __construct(MapGroupRepository $mapGroupRepository, ManagerRegistry $doctrine)
    {
        $this->mapGroupRepository = $mapGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {
        $mapGroup = $this->mapGroupRepository->findOneBy(['uuid' => $uuid]);

        $this->doctrine->getManager()->remove($mapGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 202,
            "message" => "MapGroup deleted",
        ]);
    }
}
