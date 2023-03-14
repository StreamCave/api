<?php

namespace App\Controller;

use App\Repository\TweetGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteTweetGroup extends AbstractController
{
    public function __construct(TweetGroupRepository $tweetGroupRepository, ManagerRegistry $doctrine)
    {
        $this->tweetGroupRepository = $tweetGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke($uuid): JsonResponse
    {
        $tweetGroup = $this->tweetGroupRepository->findOneBy(['uuid' => $uuid]);
        $widgets = $tweetGroup->getWidgets();
        foreach ($widgets as $widget) {
            $widget->setTweetGroup(null);
            $this->doctrine->getManager()->persist($widget);
        }

        $this->doctrine->getManager()->remove($tweetGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 202,
            "message" => "TweetGroup deleted",
        ]);
    }
}
