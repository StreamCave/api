<?php

namespace App\Controller;

use App\Repository\TweetGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditTweetGroup extends AbstractController
{
    public function __construct(TweetGroupRepository $tweetGroupRepository, ManagerRegistry $doctrine)
    {
        $this->tweetGroupRepository = $tweetGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke(Request $request, $uuid, $overlayUuid): Response
    {
        $data = json_decode($request->getContent(), true);
        $tweetGroup = $this->tweetGroupRepository->findOneBy(['uuid' => $uuid, 'overlayId' => $overlayUuid]);
        // Edit the twitch group
        $tweetGroup->setVisible($data['visible']);

        $this->doctrine->getManager()->persist($tweetGroup);
        $this->doctrine->getManager()->flush();

        return $this->json([
            "statusCode" => 201,
            "data" => $tweetGroup
        ]);
    }
}
