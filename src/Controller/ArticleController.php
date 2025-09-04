<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
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
    public function index(ArticleRepository $repo): Response
    {
        $articles = $repo->findAll();
        
        return $this->render('article/index.html.twig',
        [
            'articles' => $articles
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
    public function view(ArticleRepository $repo, int $id): Response
    {
        $article = $repo->find($id);
        
        return $this->render('article/view.html.twig',
        [
            'article' => $article
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
        $articles = $repo->findMyArticles($this->getUser());

        return $this->render("article/my_articles.html.twig",
        [
            "articles" => $articles
        ]);
    }
}
