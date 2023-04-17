<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TokenService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

class ApiLoginController extends AbstractController
{

    public function __construct()
    {
        $this->token = null;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user, Request $request, UserRepository $userRepository, ManagerRegistry $doctrine, TokenService $tokenService): Response
    {
        if (!$request->get('email') || !$request->get('password')) {
            return new JsonResponse([
                'statusCode' => 400,
                'message' => 'Veuillez fournir un "username" et un "password"'
            ], 400);
        }
        $email = $request->get('email');
        $password = $request->get('password');
        $user = $userRepository->findOneBy(['email' => $email]);

        password_verify($password, $user[0]->getPassword()) ? $accessOk = true : $accessOk = false;

        if (!$accessOk) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }


        if ($userRepository->findOneBy(['token' => $this->token])) {
            return $tokenService->generateToken();
        }

        $em = $doctrine->getManager();
        $user->setToken($this->token);
        $em->persist($user);
        $em->flush();



        return $this->json([
            'user' => $user,
            'token' => $this->token
        ]);
    }

}