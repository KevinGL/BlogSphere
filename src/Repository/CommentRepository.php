<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Cast\Array_;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    //    /**
    //     * @return Comment[] Returns an array of Comment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Comment
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByList(string $list): array
    {
        return $this->createQueryBuilder("c")
            ->groupBy("c." . $list)
            ->getQuery()
            ->getResult();
    }

    public function findByAuthor(User $author): array
    {
        return $this->createQueryBuilder("c")
            ->where("c.author = :author")
            ->setParameter("author", $author)
            ->getQuery()
            ->getResult();
    }

    public function findByGroupsFiltersPage(string $groupBy = "", array $filter = [], $page = 1): array
    {
        $limit = 10;

        $qb = $this->createQueryBuilder('c');

        if($groupBy != "")
        {
            $qb->groupBy("c." . $groupBy);
        }

        if($filter != [])
        {
            if($filter[0] == "author")
            {
                $qb->where("c.author = :value")
                ->setParameter("value", $filter[1]);
            }
        }

        $qb2 = clone $qb;

        $qb2->select('c.id');
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
