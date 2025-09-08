<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ["label" => "Titre"])
            ->add('content', TextareaType::class, ["label" => "Contenu"])
            ->add('image', FileType::class,
                [
                    'mapped' => false,
                    'required' => false,
                ])
            ->add('categories', EntityType::class,
                [
                    "label" => "Catégories",
                    'class' => Category::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => false,
                    'by_reference' => false
                ])
            ->add("save", SubmitType::class, [
                    "label" => "Sauvegarder",
                    "attr" =>
                    [
                        "class" => "bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg shadow"
                    ]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
