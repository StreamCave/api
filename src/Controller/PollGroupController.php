<?php

namespace App\Controller;

use App\Repository\PollGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PollGroupController extends AbstractController
{
    public function __construct(PollGroupRepository $pollGroupRepository)
    {
        $this->pollGroupRepository = $pollGroupRepository;
    }

    public function __invoke($uuid): Response
    {
        $user = $this->pollGroupRepository->findOneBy(["uuid" => $uuid]);
        return $this->json([
            "statusCode" => 200,
            "data" => $user
        ]);
    }
}
