<?php

namespace App\Controller;

use App\Repository\PollGroupRepository;
use App\Repository\PopupGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PopupGroupController extends AbstractController
{
    public function __construct(PopupGroupRepository $popupGroupRepository)
    {
        $this->popupGroupRepository = $popupGroupRepository;
    }

    public function __invoke($uuid): Response
    {
        $user = $this->popupGroupRepository->findOneBy(["uuid" => $uuid]);
        return $this->json([
            "statusCode" => 200,
            "data" => $user
        ]);
    }
}
