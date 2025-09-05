<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
final class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'articles')]
    public function index(Request $req, ArticleRepository $repo, UserRepository $userRepo, CategoryRepository $catRepo): Response
    {
        $articles = [];

        if(!$req->query->get("user") && !$req->query->get("cats"))
        {
            $articles = $repo->findAll();
        }
        else
        if($req->query->get("user") && !$req->query->get("cats"))
        {
            $articles = $repo->findByUser($userRepo->findBy(["username" => $req->query->get("user") ])[0]);
        }
        else
        if(!$req->query->get("user") && $req->query->get("cats"))
        {
            $articles = $repo->findByCats($catRepo->findByNames(explode(" ", $req->query->get("cats"))));
        }

        $users = $userRepo->findAll();
        $cats = $catRepo->findAll();
        
        return $this->render('article/index.html.twig',
        [
            'articles' => $articles,
            'users' => $users,
            'cats' => $cats
        ]);
    }

    #[Route("/articles/add", name: "articles_add")]
    public function add(Request $req, EntityManagerInterface $em): Response
    {
        $article = new Article();

        $article->setStatus("published");
        $article->setPublishedAt(new \DateTime());

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($article);
            $em->flush();

            $this->addFlash("success", "Article ajouté avec succès");

            return $this->redirectToRoute("articles");
        }

        return $this->render("article/add.html.twig",
        [
            "form" => $form
        ]);
    }

    #[Route('/articles/view/{id}', name: 'article_view')]
    public function view(ArticleRepository $repo, Request $req, EntityManagerInterface $em, int $id): Response
    {
        $article = $repo->find($id);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setCreatedAt(new \DateTime());
            $comment->setAuthor($this->getUser());
            $comment->setArticle($article);

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute("article_view", ["id" => $id]);
        }
        
        return $this->render('article/view.html.twig',
        [
            'article' => $article,
            "form" => $form
        ]);
    }

    #[Route("/articles/edit/{id}", name: "article_edit")]
    public function edit(Request $req, EntityManagerInterface $em, ArticleRepository $repo, int $id): Response
    {
        $article = $repo->find($id);

        //dd($article->getCategories());

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($article);
            $em->flush();

            $this->addFlash("success", "Article mis à jour avec succès");

            return $this->redirectToRoute("articles");
        }

        return $this->render("article/edit.html.twig",
        [
            "form" => $form
        ]);
    }

    #[Route("/articles/delete/{id}", name: "article_delete")]
    public function delete(EntityManagerInterface $em, ArticleRepository $repo, int $id): Response
    {
        $article = $repo->find($id);

        $em->remove($article);
        $em->flush();

        $this->addFlash("success", "Article retiré avec succès");

        return $this->redirectToRoute("articles");
    }

    #[Route("/my_articles", name:"my_articles")]
    public function myArticles(ArticleRepository $repo): Response
    {
        $articles = $repo->findByUser($this->getUser());

        return $this->render("article/my_articles.html.twig",
        [
            "articles" => $articles
        ]);
    }
}
