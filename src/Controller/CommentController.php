<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentController extends AbstractController
{
    #[Route('/comments', name: 'app_comment')]
    public function index(Request $req, CommentRepository $repo, UserRepository $userRepo): Response
    {
        if(!$this->getUser() || !in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            return $this->redirectToRoute("articles");
        }

        $comments = [];

        if(!$req->query->get("groupby"))
        {
            $comments = $repo->findAll();
        }
        else
        {
            $comments = $repo->findByList($req->query->get("groupby"));
        }

        if($req->query->get("user"))
        {
            $comments = $repo->findByAuthor($userRepo->findByName($req->query->get("user")));
        }

        $users = $userRepo->findAll();
        
        return $this->render('comment/index.html.twig',
        [
            'comments' => $comments,
            "users" => $users
        ]);
    }
}
