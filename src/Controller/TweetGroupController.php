<?php

namespace App\Controller;

use App\Repository\PollGroupRepository;
use App\Repository\PopupGroupRepository;
use App\Repository\TweetGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TweetGroupController extends AbstractController
{
    public function __construct(TweetGroupRepository $tweetGroupRepository)
    {
        $this->tweetGroupRepository = $tweetGroupRepository;
    }

    public function __invoke($uuid): Response
    {
        $user = $this->tweetGroupRepository->findOneBy(["uuid" => $uuid]);
        return $this->json([
            "statusCode" => 200,
            "data" => $user
        ]);
    }
}
