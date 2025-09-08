<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class UserController extends AbstractController
{
    #[Route('/users', name: 'app_user')]
    public function index(UserRepository $repo): Response
    {
        if (!$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('articles');
        }

        $users = $repo->findAll();
        
        return $this->render('user/index.html.twig',
        [
            'users' => $users
        ]);
    }

    #[Route("/users/delete/{id}", name: "delete_user")]
    public function delete(EntityManagerInterface $em, UserRepository $repo, int $id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('articles');
        }

        $user = $repo->find($id);

        $em->remove($user);
        $em->flush();

        $this->addFlash("success", "Cet utilisateur a bien été supprimé");

        return $this->redirectToRoute("app_user");
    }

    #[Route("/users/promote_admin/{id}", name: "user_promote_admin")]
    public function promoteAdmin(entityManagerInterface $em, UserRepository $repo, int $id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('articles');
        }

        $user = $repo->find($id);
        $user->setRoles(["ROLE_ADMIN"]);
        
        $em->persist($user);
        $em->flush();

        $this->addFlash("success", $user->getUsername() . " est désormais admin !");

        return $this->redirectToRoute("app_user");
    }

    #[Route("/users/revoke_admin/{id}", name: "user_revoke_admin")]
    public function revokeAdmin(entityManagerInterface $em, UserRepository $repo, int $id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('articles');
        }

        $user = $repo->find($id);
        $user->setRoles(["ROLE_USER"]);
        
        $em->persist($user);
        $em->flush();

        $this->addFlash("success", $user->getUsername() . " n'est plus admin");

        return $this->redirectToRoute("app_user");
    }
}
