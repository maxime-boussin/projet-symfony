<?php

namespace App\Repository;

use App\Entity\Cancellation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Cancellation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cancellation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cancellation[]    findAll()
 * @method Cancellation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CancellationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cancellation::class);
    }

    // /**
    //  * @return Cancellation[] Returns an array of Cancellation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Cancellation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
