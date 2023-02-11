<?php

namespace App\Controller;

use App\Repository\InfoGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class InfoGroupController extends AbstractController
{
    public function __construct(InfoGroupRepository $infoGroupRepository)
    {
        $this->infoGroupRepository = $infoGroupRepository;
    }

    public function __invoke($uuid): Response
    {
        $user = $this->infoGroupRepository->findOneBy(["uuid" => $uuid]);
        return $this->json([
            "statusCode" => 200,
            "data" => $user
        ]);
    }
}
