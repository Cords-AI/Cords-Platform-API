<?php

namespace App\Repository;

use App\Entity\EnabledIp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EnabledIp>
 *
 * @method EnabledIp|null find($id, $lockMode = null, $lockVersion = null)
 * @method EnabledIp|null findOneBy(array $criteria, array $orderBy = null)
 * @method EnabledIp[]    findAll()
 * @method EnabledIp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnabledIpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnabledIp::class);
    }

//    /**
//     * @return EnabledIp[] Returns an array of EnabledIp objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EnabledIp
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
