<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $groupBy = $req->query->get("groupby") ?? "";
        $filters = $req->query->get("user") ? ["author", $userRepo->findByName($req->query->get("user"))] : [];
        $page = $req->query->get("page") ?? "1";

        $comments = $repo->findByGroupsFiltersPage($groupBy, $filters, $page);

        $users = $userRepo->findAll();
        
        return $this->render('comment/index.html.twig',
        [
            'comments' => $comments["results"],
            "nbPages" => $comments["nbPages"],
            "users" => $users,
            "currentPage" => $page
        ]);
    }

    #[Route('/comments/delete/{id}', name: 'delete_comment')]
    public function delete(EntityManagerInterface $em, CommentRepository $repo, int $id): Response
    {
        if(!$this->getUser() || !in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            return $this->redirectToRoute("articles");
        }

        $comment = $repo->find($id);

        $em->remove($comment);
        $em->flush();

        $this->addFlash("success", "Ce commentaire a bien été supprimé");

        return $this->redirectToRoute("app_comment");
    }
}
