<?php

namespace App\Controller;

use App\Repository\ModelRepository;
use App\Repository\OverlayRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteOverlayController extends AbstractController
{
    public function __construct(OverlayRepository $overlayRepository, ModelRepository $modelRepository, ManagerRegistry $doctrine)
    {
        $this->overlayRepository = $overlayRepository;
        $this->modelRepository = $modelRepository;
        $this->doctrine = $doctrine;
    }

    #[Route('/overlay/:id', name: 'app_delete_overlay', methods: ['DELETE'])]
    public function __invoke($id): Response
    {
        $overlay = $this->overlayRepository->find($id);
        $this->deleteOverlay($overlay);

        return $this->json([
            'statusCode' => 200,
            'message' => 'Overlay deleted',
        ]);
    }

    private function deleteOverlay($overlay)
    {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($overlay);
        $entityManager->flush();
    }
}
