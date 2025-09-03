<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class AppFixtures extends Fixture
{
    private $faker;
    private PasswordHasherFactoryInterface $hasher;

    public function __construct(PasswordHasherFactoryInterface $hasher)
    {
        $this->faker = Factory::create();
        $this->hasher = $hasher;
    }
    
    public function load(ObjectManager $manager): void
    {
        $users = [];
        
        $admin = new User();

        $admin->setUsername("Kévin Gay");
        $admin->setPassword($this->hasher->getPasswordHasher($admin)->hash("admin"));
        $admin->setRoles(["ROLE_ADMIN"]);
        $manager->persist($admin);

        array_push($users, $admin);

        ////

        $recruiter = new User();
        
        $recruiter->setUsername("Recruteur");
        $recruiter->setPassword($this->hasher->getPasswordHasher($recruiter)->hash("recruteur"));
        $recruiter->setRoles(["ROLE_ADMIN"]);
        $manager->persist($recruiter);

        array_push($users, $recruiter);

        for($i = 0 ; $i < 18 ; $i++)
        {
            $user = new User();

            $user->setUsername($this->faker->name());
            $user->setPassword($this->hasher->getPasswordHasher($user)->hash("1234"));
            $manager->persist($user);

            array_push($users, $user);
        }

        $manager->flush();

        ////////////////////////////////////////////////////////////////

        for($i = 0 ; $i < 50 ; $i++)
        {
            $article = new Article();

            $article->setTitle($this->faker->title());
            $article->setContent(implode("\n", $this->faker->paragraphs(5)));
            $article->setSlug($this->faker->url());
            $article->setImage($this->faker->imageUrl());
            $article->setStatus("published");
            $article->setPublishedAt($this->faker->dateTimeBetween("-1 month", "now"));
            $article->setUser($users[rand() % count($users)]);
            
            $manager->persist($article);
        }

        $manager->flush();
    }
}
