<?php

namespace App\Controller;

use App\Repository\ModelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ModelController extends AbstractController
{
    public function __construct(ModelRepository $modelRepository)
    {
        $this->modelRepository = $modelRepository;
    }

    public function __invoke($uuid): Response
    {
        $user = $this->modelRepository->findOneBy(["uuid" => $uuid]);
        return $this->json([
            "statusCode" => 200,
            "data" => $user
        ]);
    }
}
