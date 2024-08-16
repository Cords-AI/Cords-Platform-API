<?php

namespace App\Repository;

use App\Entity\SearchFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SearchFilter>
 *
 * @method SearchFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method SearchFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method SearchFilter[]    findAll()
 * @method SearchFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchFilterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchFilter::class);
    }

//    /**
//     * @return SearchFilter[] Returns an array of SearchFilter objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SearchFilter
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
