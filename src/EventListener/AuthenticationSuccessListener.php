<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class AuthenticationSuccessListener
{
    public function __construct(UserRepository $userRepository, ManagerRegistry $doctrine)
    {
        $this->userRepository = $userRepository;
        $this->doctrine = $doctrine;
        $this->token = null;
    }
    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        $response = $event->getResponse();

        if (!$user instanceof UserInterface) {
            return;
        }

        // Remplace le token existant par le nouveau à l'user
        $em = $this->doctrine->getManager();
        if (count($user->getToken()) != 2) {
            // Ajouter le token dans le tableau à la fin
            $user->setToken(array_merge($user->getToken(), [Uuid::v4()]));
        } else {
            // On supprime l'index 0 du tableau
            $token = $user->getToken();
            array_shift($token);
            $user->setToken(array_merge($token, [Uuid::v4()]));
        }
        $user->setSsoLogin("normal");
        $em->persist($user);
        $em->flush();
        $event->setData($data);

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
}