<?php

namespace App\Controller;

use App\Repository\LibWidgetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LibWidgetController extends AbstractController
{
    public function __construct(LibWidgetRepository $libWidgetRepository)
    {
        $this->libWidgetRepository = $libWidgetRepository;
    }

    public function __invoke($uuid): Response
    {
        $user = $this->libWidgetRepository->findOneBy(["uuid" => $uuid]);
        return $this->json([
            "statusCode" => 200,
            "data" => $user
        ]);
    }
}
