<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\DiscordApiService;
use App\Service\TokenService;
use App\Service\TwitchApiService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Cookie;
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

        // Récupérer l'utilisateur en fonction du token
        $users = $userRepository->findAll();
        $userdb = null;
        foreach ($users as $user) {
            // Si le token est dans le tableau
            foreach ($user->getToken() as $token) {
                if ($token == $this->token) {
                    $userdb = $user;
                }
            }
        }
        if (!$userdb) {
            return $this->json([
                'message' => 'bad refresh_token',
            ], Response::HTTP_UNAUTHORIZED);
        }
        // Regénérer le refresh_token
        if (count($userdb->getToken()) != 2) {
            // Ajouter le token dans le tableau à la fin
            $userdb->setToken(array_merge($user->getToken(), [Uuid::v4()]));
        } else {
            // On supprime l'index 0 du tableau
            $token = $userdb->getToken();
            array_shift($token);
            $userdb->setToken(array_merge($token, [Uuid::v4()]));
        }
        $em = $doctrine->getManager();
        $em->persist($userdb);
        $em->flush();

        // Régénérer le token JWT
        $JWTtoken = $this->jwtManager->create($userdb);
        $response = new JsonResponse([
            'token' => $JWTtoken,
        ], 200);
        $response->headers->setCookie(
            new Cookie(
                'refresh_token',
                $user->getToken()[count($user->getToken()) - 1],
                new \DateTime('+1 day'),
                '/',
                $_ENV['COOKIE_DOMAIN'],
                true,
                true,
                false,
                'none'
            ));
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
            if ($response->getStatusCode() === 400) {
                return new JsonResponse([
                    'statusCode' => 200,
                    'message' => 'token revoked',
                ], Response::HTTP_OK);
            } else {
                return new JsonResponse([
                    'statusCode' => 401,
                    'message' => 'token not revoked',
                ], Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new JsonResponse([
                'statusCode' => 401,
                'message' => 'missing token_sso',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    private function generateToken(): string
    {
        $this->token = Uuid::v4();
        return $this->token;
    }
}
