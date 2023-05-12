<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TwitchApiService;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class Oauth2TwitchController extends AbstractController {

    public function __construct(TwitchApiService $twitchApiService, UserRepository $userRepository, JWTTokenManagerInterface $jwtManager, ManagerRegistry $doctrine)
    {
        $this->twitchApiService = $twitchApiService;
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
        $this->doctrine = $doctrine;
    }

    #[Route('oauth2/twitch/connect', name: 'app_oauth2_connect_twitch')]
    public function connect(Request $request): Response
    {
        $authorizationUri = $this->twitchApiService->getAuthorizationUri(['user:read:email', 'channel:manage:polls', 'moderation:read', 'channel:manage:predictions']);
        return $this->redirect($authorizationUri.'&state=sso_request');
    }

    #[Route('oauth2/twitch/connect/website', name: 'app_oauth2_connect_twitch_website')]
    public function connectWebsite(Request $request): Response
    {
        $authorizationUri = $this->twitchApiService->getAuthorizationUri(['user:read:email', 'channel:manage:polls', 'moderation:read', 'channel:manage:predictions']);
        return $this->redirect($authorizationUri.'&state=front_request');
    }

    #[Route('oauth2/twitch/check', name: 'app_oauth2_check_twitch')]
    public function check(Request $request): Response
    {
        if ($request->get('code')) {
            $em = $this->doctrine->getManager();
            $dataToken = $this->twitchApiService->getAccessToken($request->get('code'));
            $accessToken = $dataToken['access_token'];
            $refreshTokenTwitch = $dataToken['refresh_token'];
            $twitchUser = $this->twitchApiService->fetchUser($accessToken)['data'][0];

            // On check si l'adresse mail est déjà présente dans notre base de données
            $userDBEmailOnly = $this->userRepository->findOneBy(['email' => $twitchUser['email']]);
            if ($userDBEmailOnly) {
                $userDB = $userDBEmailOnly;
                // Si oui, on set le twitchId de l'utilisateur
                $userDB->setTwitchId($twitchUser['id']);
            } else {
                // Sinon, on crée un nouvel utilisateur
                $userDB = new User();
                $userDB->setEmail($twitchUser['email']);
                $userDB->setPassword("string");
                $userDB->setTwitchId($twitchUser['id']);
                $userDB->setAvatar($twitchUser['profile_image_url']);
                $userDB->setPseudo($twitchUser['display_name']);
                $userDB->setRoles(['ROLE_USER']);
            }

            // On set le refreshToken de l'utilisateur
            $userDB->setToken(Uuid::v4());
            $userDB->setSsoLogin("twitch");
            // AccessToken, RefreshToken et expiresIn de Twitch
            $userDB->setTwitchAccessToken($accessToken);
            $userDB->setTwitchRefreshToken($refreshTokenTwitch);
            $userDB->setTwitchExpiresIn(time() + $dataToken['expires_in']);
            $em->persist($userDB);
            $em->flush();

            $refreshToken = $userDB->getToken();

            // On génère un token JWT
            $token = $this->jwtManager->create($userDB);

            // On génère un cookie avec le token JWT
            $response = new RedirectResponse($_ENV['TWITCH_SUCCESS_REDIRECT_URI']);
            if($request->get('state') == "sso_request") {
                $response->headers->setCookie(
                    new Cookie(
                        'refresh_token',
                        $refreshToken,
                        new \DateTime('+1 day'),
                        '/',
                        "localhost",
                        true,
                        true,
                        false,
                        'none'
                    )
                );
            }
            $response->headers->setCookie(
                new Cookie(
                    'broadcaster_id',
                    $twitchUser['id'],
                    new \DateTime('+1 day'),
                    '/',
                    "localhost",
                    true,
                    false,
                    false,
                    'none'
                ));

            $response->headers->setCookie(
                new Cookie(
                    't_access_token_sso',
                    $accessToken,
                    new \DateTime('+1 day'),
                    '/',
                    "localhost",
                    true,
                    false,
                    false,
                    'none'
                ));

            $response->headers->setCookie(
                new Cookie(
                    't_refresh_token_sso',
                    $refreshTokenTwitch,
                    new \DateTime('+1 day'),
                    '/',
                    "localhost",
                    true,
                    false,
                    false,
                    'none'
                ));
            return $response;
        }
        return new Response("Error");
    }
}