<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Like;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Form\LikeType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\LikeRepository;
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
        $page = 1;
        $nbPages = 1;

        if($req->query->get("page"))
        {
            $page = $req->query->get("page");
        }

        if(!$req->query->get("user") && !$req->query->get("cats"))
        {
            $articles = $repo->findPagination($page);
            $nbPages = ceil(count($repo->findAll()) / 10);
        }
        else
        if($req->query->get("user") && !$req->query->get("cats"))
        {
            $articles = $repo->findByUser($userRepo->findBy(["username" => $req->query->get("user") ])[0], $page);
            $nbPages = ceil(count($articles) / 10);
        }
        else
        if(!$req->query->get("user") && $req->query->get("cats"))
        {
            $articles = $repo->findByCats($catRepo->findByNames(explode(" ", $req->query->get("cats"))), $page);
            $nbPages = ceil(count($articles) / 10);
        }

        $users = $userRepo->findAll();
        $cats = $catRepo->findAll();

        //dd($nbPages);
        
        return $this->render('article/index.html.twig',
        [
            'articles' => $articles,
            'users' => $users,
            'cats' => $cats,
            'nbPages' => $nbPages
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
    public function view(ArticleRepository $repo, LikeRepository $likeRepo, Request $req, EntityManagerInterface $em, int $id): Response
    {
        $article = $repo->find($id);

        $comment = new Comment();

        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($req);

        $like = $likeRepo->findByUserArticle($this->getUser(), $article);
        if(!$like)
        {
            $like = new Like();
        }

        $formLike = $this->createForm(LikeType::class, $like);
        $formLike->handleRequest($req);

        $nbLikes = 0;
        $nbDislikes = 0;
        foreach($article->getLikes() as $l)
        {
            if($l->getValue() == 1)
            {
                $nbLikes++;
            }

            else
            if($l->getValue() == -1)
            {
                $nbDislikes++;
            }
        }

        if($formComment->isSubmitted() && $formComment->isValid())
        {
            $comment->setCreatedAt(new \DateTime());
            $comment->setAuthor($this->getUser());
            $comment->setArticle($article);

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute("article_view", ["id" => $id]);
        }

        if($formLike->isSubmitted() && $formLike->isValid())
        {
            $like->setAuthor($this->getUser());
            $like->setArticle($article);

            if($formLike->get('like')->isClicked())
            {
                $like->setValue(1);
            }
            else
            if($formLike->get('dislike')->isClicked())
            {
                $like->setValue(-1);
            }

            $em->persist($like);
            $em->flush();

            return $this->redirectToRoute("article_view", ["id" => $id]);
        }
        
        return $this->render('article/view.html.twig',
        [
            'article' => $article,
            "formComment" => $formComment,
            "formLike" => $formLike,
            "nbLikes" => $nbLikes,
            "nbDislikes" => $nbDislikes
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
    public function myArticles(Request $req, ArticleRepository $repo): Response
    {
        $page = 1;

        if($req->query->get("page"))
        {
            $page = $req->query->get("page");
        }
        
        $articles = $repo->findByUser($this->getUser(), $page);

        return $this->render("article/my_articles.html.twig",
        [
            "articles" => $articles
        ]);
    }
}
