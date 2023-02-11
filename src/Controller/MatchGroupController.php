<?php

namespace App\Controller;

use App\Repository\MatchGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MatchGroupController extends AbstractController
{
    public function __construct(MatchGroupRepository $matchGroupRepository)
    {
        $this->matchGroupRepository = $matchGroupRepository;
    }

    public function __invoke($uuid): Response
    {
        $user = $this->matchGroupRepository->findOneBy(["uuid" => $uuid]);
        return $this->json([
            "statusCode" => 200,
            "data" => $user
        ]);
    }
}
