<?php

namespace App\Repository;

use App\Entity\Excursion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method Excursion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Excursion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Excursion[]    findAll()
 * @method Excursion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExcursionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Excursion::class);
    }

    /**
     * @param $user
     * @param $site
     * @param string|null $content
     * @param \DateTime $from
     * @param \DateTime $to
     * @param bool $owned
     * @param bool $subscribed
     * @param bool $notSubscribed
     * @param bool $past
     * @return Excursion[]
     * @throws \Exception
     */
    public function findByFilters($user, $site, ?string $content, \DateTime $from, \DateTime $to, bool $owned, bool $subscribed, bool $notSubscribed, bool $past){
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.site = :site');
        $qb->setParameter('site', $site);
        $qb->andWhere('e.date BETWEEN :from AND :to');
        $qb->setParameter('from', $from->format('Y-m-d'));
        $qb->setParameter('to', $to->format('Y-m-d'));
        $qb->andWhere('1=0');
        if($content != null){
            $qb->orWhere('e.name LIKE :content');
            $qb->setParameter('content', '%'.$content.'%');
        }
        if($owned){
            $qb->orWhere('e.owner = :user');
            $qb->setParameter('user', $user);
        }
        if($subscribed){
            $qb->orWhere($qb->expr()->in(':user', 'e.participants'));
            $qb->setParameter('user', $user);
        }
        if($notSubscribed){
            $qb->orWhere($qb->expr()->notIn(':user', 'e.participants'));
            $qb->setParameter('user', $user);
        }
        if($past){
            $qb->orWhere('e.date < :now');
            $qb->setParameter('now', new \DateTime());;
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * @param $user
     * @param $site
     * @param string|null $content
     * @param \DateTime $from
     * @param \DateTime $to
     * @param bool $owned
     * @param bool $subscribed
     * @param bool $notSubscribed
     * @param bool $past
     * @return mixed
     * @throws \Exception
     */
    public function nativeFindByFilters($user, $site, ?string $content, \DateTime $from, \DateTime $to, bool $owned, bool $subscribed, bool $notSubscribed, bool $past){
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT excursion.id, site.name as site_name, date, limit_date as limitDate, duration, excursion.name as name, description, visibility, participant_limit, state, organizer_id,
            user.first_name as organizer_first_name, user.last_name as organizer_last_name, user.nickname as organizer_nickname, 
            (select COUNT(*) from excursion_user eu where eu.excursion_id=excursion.id) nb_participants,
            :user IN(select user_id from excursion_user eu where eu.excursion_id=excursion.id) as subscribed
            FROM excursion 
            LEFT JOIN excursion_user ON excursion.id = excursion_user.excursion_id
            JOIN site ON site.id = excursion.site_id
            JOIN user ON user.id = excursion.organizer_id
            WHERE excursion.site_id = :site
            AND (date BETWEEN :from AND :to)
            AND visibility = 1
            AND excursion.name LIKE :content
            AND (1=0';
        if($owned){
            $sql.=' OR excursion.organizer_id = :user';
        }
        if($subscribed){
            $sql.=' OR user_id = :user';
        }
        if($notSubscribed){
            $sql.=' OR user_id != :user OR user_id IS NULL';
        }
        if($past){
            $sql.=' OR excursion.date < NOW()';
        }
        $sql.=') GROUP BY excursion.id LIMIT 50';
        $from = $from->format('Y-m-d');
        $to = $to->format('Y-m-d');
        $content = '%'.$content.'%';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':site', $site);
        $stmt->bindParam(':from', $from);
        $stmt->bindParam(':to', $to);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':user', $user);
        $stmt->execute();
        $res = $stmt->fetchAll();
        foreach($res as $excursion){
            $state = $this-> updateState($excursion['id']);
            $excursion['state'] = $state;
        }
        return $res;
    }

    /**
     * @param $id
     * @return int|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateState($id){
        $excursion = $this->find($id);
        if($excursion != null){
            if($excursion->getState() != 0 && $excursion->getState() != 5){
                if( $excursion->getLimitDate() > new \DateTime())
                    $excursion->setState(1);
                if( $excursion->getLimitDate() < new \DateTime())
                    $excursion->setState(2);
                if( $excursion->getDate() < new \DateTime())
                    $excursion->setState(3);
                if( $excursion->getDate()->add($excursion->getDuration()) < new \DateTime())
                    $excursion->setState(4);
            }
            $this->getEntityManager()->flush($excursion);
            return $excursion->getState();
        }
    }

    /**
     * @param $id
     * @return Excursion|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateAndFind($id){
        $this->updateState($id);
        return $this->find($id);
    }

    /**
     * @param \DateTime $date
     * @return int
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function purge(\DateTime $date){
        $qb = $this->createQueryBuilder('e')
            ->where('e.date < :date')
            ->setParameter('date', $date->format('Y-m-d'));
        $excursions = $qb->getQuery()->getResult();
        $nb = 0;
        foreach($excursions as $excursion){
            $this->getEntityManager()->remove($excursion);
            $nb++;
        }
        $this->getEntityManager()->flush();
        return $nb;
    }
    // /**
    //  * @return Excursion[] Returns an array of Excursion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Excursion
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
