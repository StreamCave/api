<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DiscordApiService;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class Oauth2DiscordController extends AbstractController
{
    public function __construct(private readonly DiscordApiService $discordApiService, UserRepository $userRepository, JWTTokenManagerInterface $jwtManager, ManagerRegistry $doctrine)
    {
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
        $this->doctrine = $doctrine;
    }

    #[Route('oauth2/discord/connect', name: 'app_oauth2_connect_discord')]
    public function connect(): Response
    {
        $authorizationUri = $this->discordApiService->getAuthorizationUri(['identify', 'email']);

        return $this->redirect($authorizationUri);
    }

    #[Route('oauth2/discord/check', name: 'app_oauth2_check_discord')]
    public function check(Request $request): Response
    {
        if ($request->get('code')) {
            $em = $this->doctrine->getManager();
            $dataToken = $this->discordApiService->getAccessToken($request->get('code'));
            $accessToken = $dataToken['access_token'];
            $refreshTokenDiscord = $dataToken['refresh_token'];

            // DATA DISCORD USER
            $discordUser = $this->discordApiService->fetchUser($accessToken);

            // On check si l'adresse mail est déjà présente dans notre base de données
            $userDBEmailOnly = $this->userRepository->findOneBy(['email' => $discordUser['email']]);
            if ($userDBEmailOnly) {
                $userDB = $userDBEmailOnly;
                // Si oui, on set le discordId de l'utilisateur
                $userDB->setDiscordId($discordUser['id']);
            } else {
                // Sinon, on crée un nouvel utilisateur
                $userDB = new User();
                $userDB->setEmail($discordUser['email']);
                $userDB->setPassword("string");
                $userDB->setDiscordId($discordUser['id']);
                $userDB->setPseudo($discordUser['username']);
                $userDB->setRoles(['ROLE_USER']);
            }

            // On génère un refreshToken dans la base de données pour l'utilisateur
            $userDB->setAvatar("https://cdn.discordapp.com/avatars/" . $discordUser['id'] ."/" . $discordUser['avatar'] .".png");
            $userDB->setToken(Uuid::v4());
            $userDB->setSsoLogin("discord");
            $em->persist($userDB);
            $em->flush();
            $refreshToken = $userDB->getToken();

            // On génère un token JWT
            $token = $this->jwtManager->create($userDB);
            
            // Créer un cookie
            $response = new RedirectResponse($_ENV["DISCORD_SUCCESS_REDIRECT_URI"]);
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
                ));
            $response->headers->setCookie(
                new Cookie(
                    'access_token_sso',
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
                    'refresh_token_sso',
                    $refreshTokenDiscord,
                    new \DateTime('+1 day'),
                    '/',
                    "localhost",
                    true,
                    false,
                    false,
                    'none'
                ));
            // On retour un json avec le cookie
            // $response->setContent(json_encode([
            //     'token' => $token,
            //     'refresh_token' => $refreshToken,
            //     'refresh_token_discord' => $refreshTokenDiscord,
            // ]));
            // $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        return new Response('Error');
    }
}
