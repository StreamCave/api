<?php

namespace App\Controller;

use App\Repository\PollGroupRepository;
use App\Repository\PopupGroupRepository;
use App\Repository\TweetGroupRepository;
use App\Repository\WidgetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class WidgetController extends AbstractController
{
    public function __construct(WidgetRepository $widgetRepository)
    {
        $this->widgetRepository = $widgetRepository;
    }

    public function __invoke($uuid): Response
    {
        $user = $this->widgetRepository->findOneBy(["uuid" => $uuid]);
        return $this->json([
            "statusCode" => 200,
            "data" => $user
        ]);
    }
}
