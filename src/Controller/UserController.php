<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig',
        [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/api/users/{name}', name: 'api_users')]
    public function api(UserRepository $repo, string $name): Response
    {
        $users = $repo->findByName($name);

        return new JsonResponse(array_map(function ($user)
        {
            return $user->getUsername();
        }, $users));
    }
}
