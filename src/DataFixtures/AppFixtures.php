<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Like;
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

        /*$categories = [];
        
        for($i = 0 ; $i < 10 ; $i++)
        {
            $category = new Category();
            $category->setName($this->faker->word());

            $manager->persist($category);

            array_push($categories, $category);
        }

        ////////////////////////////////////////////////////////////////

        $articles = [];

        for($i = 0 ; $i < 50 ; $i++)
        {
            $article = new Article();

            $article->setTitle($this->faker->sentence());
            $article->setContent(implode("\n", $this->faker->paragraphs(5)));
            $article->setImage("https://picsum.photos/id/" . $i . "/800/600");
            $article->setStatus("published");
            $article->setPublishedAt($this->faker->dateTimeBetween("-1 month", "now"));
            $article->setUser($users[rand() % count($users)]);

            $nbCat = rand() % 2 + 3;

            for($j = 0 ; $j < $nbCat ; $j++)
            {
                $cat = $categories[rand() % count($categories)];
                $article->addCategory($cat);
            }

            array_push($articles, $article);
            
            $manager->persist($article);
        }

        ////////////////////////////////////////////////////////////////

        foreach($users as $user)
        {
            $nbComments = rand() % 5;

            for($i = 0 ; $i < $nbComments ; $i++)
            {
                $comment = new Comment();

                $comment->setArticle($articles[rand() % count($articles)]);
                $comment->setAuthor($user);
                $comment->setContent($this->faker->paragraph());
                $comment->setCreatedAt($this->faker->dateTimeBetween("-1 month", "now"));

                $manager->persist($comment);
            }
        }*/

        ////////////////////////////////////////////////////////////////

        $articles = [];
        $categories = [];
        $comments = [];

        $file = fopen("./blogsphere_articles_100.csv", "r");

        $index = 0;

        while(1)
        {
            $line = fgetcsv($file);
            if(!$line)
            {
                break;
            }

            if($index > 0)
            {
                $article = new Article();

                $article->setTitle($line[0]);
                $article->setContent($line[1]);
                $article->setImage("https://picsum.photos/id/" . 2 * $index . "/800/600");
                $article->setStatus("published");
                $article->setPublishedAt($this->faker->dateTimeBetween("-1 month", "now"));
                $article->setUser($users[rand() % count($users)]);

                $cats = explode(" | ", $line[2]);

                foreach($cats as $cat)
                {
                    $existingCategory = null;

                    foreach($categories as $c)
                    {
                        if ($c->getName() == $cat)
                        {
                            $existingCategory = $c;
                            break;
                        }
                    }

                    if ($existingCategory)
                    {
                        $article->addCategory($existingCategory);
                    }
                    else
                    {
                        $category = new Category();
                        $category->setName($cat);
                        $article->addCategory($category);

                        $categories[] = $category;
                        $manager->persist($category);
                    }
                }

                ///////////////////

                $coms = explode(" | ", $line[3]);

                foreach($coms as $com)
                {
                    $comment = new Comment();
                    $comment->setContent($com);
                    $comment->setCreatedAt($this->faker->dateTimeBetween("-1 month", "now"));
                    $comment->setAuthor($users[rand() % count($users)]);
                    
                    $article->addComment($comment);

                    if(!in_array($comment, $comments))
                    {
                        array_push($comments, $comment);
                        $manager->persist($comment);
                    }
                }

                array_push($articles, $article);
                
                $manager->persist($article);
            }

            $index++;
        }

        fclose($file);

        ////////////////////////////////////////////////////////////////

        for($i = 0 ; $i < 400 ; $i++)
        {
            $like = new Like();

            $value = rand() % 2 == 0 ? 1 : -1;

            $like->setArticle($articles[rand() % count($articles)]);
            $like->setAuthor($users[rand() % count($users)]);
            $like->setValue($value);

            $manager->persist($like);
        }

        $manager->flush();
    }
}
