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
            AND visibility = 1
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
        foreach($res as $key => $excursion){
            $state = $this-> updateState($excursion['id']);
            $res[$key]['state'] = $state;
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
            $initialState = $excursion->getState();
            if($initialState != 0 && $initialState != 5){
                if( $excursion->getLimitDate() > new \DateTime())
                    $excursion->setState(1);
                if( $excursion->getLimitDate() < new \DateTime())
                    $excursion->setState(2);
                if( $excursion->getDate() < new \DateTime())
                    $excursion->setState(3);
                if( $excursion->getDate()->add($excursion->getDuration()) < new \DateTime())
                    $excursion->setState(4);
            }
            if($excursion->getState() !== $initialState)
                $this->getEntityManager()->flush($excursion);
            return $excursion->getState();
        }
        return false;
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
            $excursion->setVisibility(0);
            $nb++;
        }
        $this->getEntityManager()->flush();
        return $nb;
    }


    public function getNbYear()
    {
        $from = new \DateTime();
        $to = new \DateTime();
        $from->sub(new \DateInterval('P1Y'));
        return $this->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->andWhere('e.date BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNbMonth()
    {
        $from = new \DateTime();
        $to = new \DateTime();
        $from->sub(new \DateInterval('P1M'));
        return $this->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->andWhere('e.date BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTopUser()
    {
        $max = 0;
        $topUser = "";
        //id, date, limit_date, duration, e0_.name AS name_4, e0_.description AS description_5, e0_.visibility AS visibility_6, e0_.participant_limit AS participant_limit_7, e0_.state AS state_8, u1_.nickname AS nickname_9, count(e0_.organizer_id) AS sclr_10, e0_.site_id AS site_id_11, e0_.organizer_id AS organizer_id_12, e0_.place_id AS place_id_13
        $count = $this->getEntityManager()->createQueryBuilder()
            ->select('u.nickname')
            ->from('App:User', 'u')
            ->addSelect('count(e) as counter')
            ->groupBy('u.id')
            ->leftJoin('u.ownedExcursions', 'e')
            ->getQuery()
            ->getResult();
        foreach ($count as $key => $value) {
            if ($value["counter"] > $max) {
                $max = $value["counter"];
                $topUser = $value["nickname"];
            }
        }
        return $topUser . " (" . $max . ")";
    }

    public function getYearCut()
    {
        $res = "{";
        $year = intval(date("Y"))-1;
        $month = intval(date("m"))+1;
        for ($i=1; $i <= 12; $i++) {
            if($month == 13){
                $month = 1;
                $year++;
            }
            $from= new \Datetime($year.'-'.$month.'-01');
            $to= new \Datetime(($month==12?$year+1:$year).'-'.($month==12?1:$month+1).'-01');
            $to->sub(new \DateInterval('P1D'));
            $nb = $this->createQueryBuilder('e')
                ->select('COUNT(e)')
                ->andWhere('e.date BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to )
                ->getQuery()
                ->getSingleScalarResult();


            //dump("from ".date_format($from, 'd/m/Y')." to ".date_format($to, 'd/m/Y')." -> ".$nb);
            $res .= '"'.date('M', mktime(0, 0, 0, $month, 10)).'" : '.$nb.", ";
            $month++;
        }
        return $res."}";
    }
    function getNbMessages(){
        return $this->getEntityManager()->createQueryBuilder()
            ->from('App:Message', 'm')
            ->select('COUNT(m)')
            ->getQuery()
            ->getSingleScalarResult();
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
