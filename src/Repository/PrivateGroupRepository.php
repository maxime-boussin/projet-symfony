<?php

namespace App\Repository;

use App\Entity\PrivateGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PrivateGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrivateGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrivateGroup[]    findAll()
 * @method PrivateGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrivateGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrivateGroup::class);
    }

    // /**
    //  * @return PrivateGroup[] Returns an array of PrivateGroup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PrivateGroup
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
