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

    public function findByUser(User $user, string $page): array
    {
        $limit = 10;

        return $this->createQueryBuilder("a")
            ->where("a.user = :user")
            ->setParameter("user", $user)
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByFiltersPage(array $filter = [], $page = 1): array
    {
        $limit = 10;

        $qb = $this->createQueryBuilder('a');

        if($filter != [])
        {
            if($filter[0] == "user")
            {
                $qb->where("a.user = :value")
                ->setParameter("value", $filter[1]);
            }

            else
            if($filter[0] == "cats")
            {
                $qb->innerJoin('a.categories', 'c')
                    ->andWhere('c.id IN (:cats)')
                    ->setParameter('cats', $filter[1]);
            }
        }

        $qb2 = clone $qb;

        $qb2->select('a.id');
        $totalResults = count($qb2->getQuery()->getResult());

        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);

        $results = $qb->getQuery()->getResult();

        return [
            "results" => $results,
            "nbPages" => ceil($totalResults / $limit)
        ];
    }
}
