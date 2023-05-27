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
        } else {
            $this->userRepository->find($user->getId())->setToken(Uuid::v4());
        }

        // Vérifie si le token existe déjà et si oui le regénère
        if ($this->userRepository->findOneBy(['token' => $this->token])) {
            $this->token = Uuid::v4();
        }

        // Remplace le token existant par le nouveau à l'user
        $em = $this->doctrine->getManager();
        $user->setToken($this->token);
        $em->persist($user);
        $em->flush();
        $event->setData($data);

        $response->headers->setCookie(
            new Cookie(
                'refresh_token',
                $user->getToken(),
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