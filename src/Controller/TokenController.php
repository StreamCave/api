<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\DiscordApiService;
use App\Service\TokenService;
use App\Service\TwitchApiService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class TokenController extends AbstractController
{
    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, DiscordApiService $discordApiService, TwitchApiService $twitchApiService)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->discordApiService = $discordApiService;
        $this->twitchApiService = $twitchApiService;
        $this->token = null;
    }

    #[Route('/api/refresh_token', name: 'app_token', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine, TokenService $tokenService): Response
    {
        $this->token = $tokenService->translateTokenFromCookie($request->cookies->get('refresh_token'));

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


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/api/discord/refresh_token', name: 'app_discord_token', methods: ['POST'])]
    public function discordToken(Request $request): JsonResponse
    {
        $httpClient = HttpClient::create();
        $data = json_decode($request->getContent(), true);
        if ($data['refresh_token_sso']) {
            $response = $this->discordApiService->refreshToken($data['refresh_token_sso']);
        } else {
            return new JsonResponse([
                'message' => 'missing refresh_token_sso',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'token' => $response,
        ], Response::HTTP_OK);
    }


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/api/discord/revoke_token', name: 'app_discord_token_revoke', methods: ['POST'])]
    public function revokeDiscordToken(Request $request): JsonResponse
    {
        $httpClient = HttpClient::create();
        $data = json_decode($request->getContent(), true);
        if ($data['token_sso']) {
            $response = $this->discordApiService->revokeToken($data['token_sso']);
        } else {
            return new JsonResponse([
                'message' => 'missing token_sso',
            ], Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse([
            'token' => $response,
        ], Response::HTTP_OK);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/api/twitch/refresh_token', name: 'app_twitch_token', methods: ['POST'])]
    public function refreshTwitchToken(Request $request): JsonResponse
    {
        $httpClient = HttpClient::create();
        $data = json_decode($request->getContent(), true);
        if ($data['refresh_token_sso']) {
            $response = $this->twitchApiService->refreshToken($data['refresh_token_sso']);
        } else {
            return new JsonResponse([
                'message' => 'missing refresh_token_sso',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'token' => $response,
        ], Response::HTTP_OK);
    }

    #[Route('/api/twitch/revoke_token', name: 'app_twitch_token_revoke', methods: ['POST'])]
    public function revokeTwitchToken(Request $request): JsonResponse
    {
        $httpClient = HttpClient::create();
        $data = json_decode($request->getContent(), true);
        if ($data['token_sso']) {
            $response = $this->twitchApiService->revokeToken($data['token_sso']);
        } else {
            return new JsonResponse([
                'message' => 'missing token_sso',
            ], Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse([
            'token' => $response,
        ], Response::HTTP_OK);
    }

    private function generateToken(): string
    {
        $this->token = Uuid::v4();
        return $this->token;
    }
}
