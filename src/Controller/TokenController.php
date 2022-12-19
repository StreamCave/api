<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\TokenService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;

class TokenController extends AbstractController
{
    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->token = null;
    }

    #[Route('/api/refresh_token', name: 'app_token', methods: ['POST'])]
    public function index(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine, TokenService $tokenService): Response
    {
        $this->token = $tokenService->translateTokenFromCookie($request->headers->get('set-cookie'));

        $user = $userRepository->findOneBy(['token' => $this->token]);
        // Régénérer le refresh_token
        if (!$user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Régénérer le token JWT
        $JWTtoken = $this->jwtManager->create($user);
        $response = new JsonResponse([
            'token' => $JWTtoken,
        ], 200);
        return $response;
    }

    private function generateToken(): string
    {
        $this->token = Uuid::v4();
        return $this->token;
    }
}
