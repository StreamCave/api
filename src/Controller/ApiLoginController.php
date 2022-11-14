<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {

        if (!$request->get('email') || !$request->get('password')) {
            return new JsonResponse([
                'statusCode' => 400,
                'message' => 'Veuillez fournir un "username" et un "password"'
            ], 400);
        }
        $email = $request->get('email');
        $password = $request->get('password');
        $user = $userRepository->findBy(['email' => $email]);

        password_verify($password, $user[0]->getPassword()) ? $accessOk = true : $accessOk = false;

        if (!$accessOk) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = Uuid::v4();
        $user[0]->setToken($token);

        $em = $doctrine->getManager();
        $em->persist($user[0]);
        $em->flush();

        return $this->json([
            'user' => $user[0],
            'token' => $token
        ]);
    }
}