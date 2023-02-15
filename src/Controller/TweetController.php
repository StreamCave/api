<?php

namespace App\Controller;

use App\Repository\TweetGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TweetController extends AbstractController
{
    public function __construct(TweetGroupRepository $tweetGroupRepository)
    {
        $this->tweetGroupRepository = $tweetGroupRepository;
    }

    public function __invoke($overlayId): JsonResponse
    {
        $tweetsData = [];
        $tweets = $this->tweetGroupRepository->findBy(['overlayId' => $overlayId]);
        foreach ($tweets as $tweet) {
            $tweetsData[] = [
                'uuid' => $tweet->getUuid(),
                'overlayId' => $tweet->getOverlayId(),
                'content' => $tweet->getContent(),
                'avatar' => $tweet->getAvatar(),
                'pseudo' => $tweet->getPseudo(),
            ];
        }
        return new JsonResponse([
            'statusCode' => 200,
            'message' => $tweetsData
        ], 201);
    }
}
