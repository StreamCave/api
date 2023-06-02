<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Uid\Uuid;

class JWTCreatedListener {

    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack, UserRepository $userRepository)
    {
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();


        $expiration = new \DateTime('+1 day');
        $user = $event->getUser();
        $payload       = $event->getData();
        $payload['pseudo'] = $user->getPseudo();
        $payload['roles'] = $user->getRoles();
        $payload['ip'] = $request->getClientIp();
        $payload['uuid'] = $user->getUuid();
        $payload['userId'] = $user->getId();
        $payload['ssoLogin'] = $user->getSsoLogin();
        $payload['twitchId'] = $user->getTwitchId();
        $payload['avatar'] = $user->getAvatar();
        $payload['uuid'] = $user->getUuid();
        $payload['exp'] = $expiration->getTimestamp();

        $event->setData($payload);

        $header        = $event->getHeader();
        $header['cty'] = 'JWT';

        $event->setHeader($header);
    }
}