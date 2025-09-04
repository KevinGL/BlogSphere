<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
final class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'app_category')]
    public function index(CategoryRepository $repo): Response
    {
        $cats = $repo->findAll();
        
        return $this->render('category/index.html.twig',
        [
            'cats' => $cats
        ]);
    }

    #[Route('/categories/add', name: 'cat_add')]
    public function add(Request $req, EntityManagerInterface $em): Response
    {
        $cat = new Category();

        $form = $this->createForm(CategoryType::class, $cat);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($cat);
            $em->flush();
        }
        
        return $this->render('category/add.html.twig',
        [
            'form' => $form
        ]);
    }

    #[Route('/categories/edit/{id}', name: 'cat_edit')]
    public function view(Request $req, CategoryRepository $repo, EntityManagerInterface $em, int $id): Response
    {
        $cat = $repo->find($id);

        $form = $this->createForm(CategoryType::class, $cat);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($cat);
            $em->flush();
        }
        
        return $this->render('category/edit.html.twig',
        [
            'form' => $form
        ]);
    }

    #[Route("/categories/delete/{id}", name: "cat_delete")]
    public function delete(EntityManagerInterface $em, CategoryRepository $repo, int $id): Response
    {
        $cat = $repo->find($id);

        $em->remove($cat);
        $em->flush();

        $this->addFlash("success", "Catégorie retirée avec succès");

        return $this->redirectToRoute("app_category");
    }
}
