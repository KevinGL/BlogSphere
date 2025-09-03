<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }
    
    public function load(ObjectManager $manager): void
    {
        for($i = 0 ; $i < 50 ; $i++)
        {
            $article = new Article();

            $article->setTitle($this->faker->title());
            $article->setContent(implode("\n", $this->faker->paragraphs(5)));
            $article->setSlug($this->faker->url());
            $article->setImage($this->faker->imageUrl());
            $article->setStatus("published");
            $article->setPublishedAt($this->faker->dateTimeBetween("-1 month", "now"));
            
            $manager->persist($article);
        }

        $manager->flush();
    }
}
