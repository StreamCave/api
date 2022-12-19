<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\TokenService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
    public function __construct()
    {
        $this->token = null;
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['GET'])]
    public function logout(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine, TokenService $tokenService)
    {

        $this->token = $tokenService->translateTokenFromCookie($request->headers->get('set-cookie'));

        $user = $userRepository->findOneBy(['token' => $this->token]);
        // Régénérer le refresh_token
        if (!$user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user->setToken(null);
        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();

        $request->headers->remove('set-cookie');

        return $this->json([
            'message' => 'logout',
        ]);
    }
}
