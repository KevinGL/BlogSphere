<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findPagination(int $page): Array
    {
        $nbByPages = 10;
        
        return $this->createQueryBuilder("a")
            ->setFirstResult($nbByPages * ($page - 1))
            ->setMaxResults($nbByPages)
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $user, int $page): Array
    {
        $nbByPages = 10;
        
        return $this->createQueryBuilder("a")
            ->where("a.user = :user")
            ->setParameter(":user", $user)
            ->setFirstResult($nbByPages * ($page - 1))
            ->setMaxResults($nbByPages)
            ->getQuery()
            ->getResult();
    }

    public function findByCats(Array $cats, int $page): Array
    {
        $nbByPages = 10;
        
        return $this->createQueryBuilder('a')
        ->innerJoin('a.categories', 'c')
        ->andWhere('c.id IN (:cats)')
        ->setFirstResult($nbByPages * ($page - 1))
        ->setMaxResults($nbByPages)
        ->setParameter('cats', $cats)
        ->getQuery()
        ->getResult();
    }
}
