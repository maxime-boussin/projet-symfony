<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findContacts(User $user){
        return $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from('App:User', 'u')
            ->leftJoin('u.messages', 'm')
            ->leftJoin('u.receivedMessages', 'm2')
            ->groupBy('u')
            ->addGroupBy('m.date')
            ->addGroupBy('m2.date')
            ->andWhere('u.id != :user')
            ->andWhere('m2.sender = :user OR m.receiver = :user')
            ->orderBy('m.date', 'ASC')
            ->addOrderBy('m2.date', 'ASC')
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getResult();
    }

    function getLastUser(User $user) {
        $lastMessage = $this->createQueryBuilder('m')
            ->orderBy('m.date', 'DESC')
            ->setMaxResults(1)
            ->andWhere('m.sender = :user OR m.receiver = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
        if($lastMessage == null)
            return false;
        return ($lastMessage->getReceiver() === $user? $lastMessage->getSender() :$lastMessage->getReceiver());
    }

    function getConversation(User $user1, User $user2, \DateTime $date=null) {
        return $this->createQueryBuilder('m')
            ->orderBy('m.date', 'ASC')
            ->where('(m.sender = :user1 AND m.receiver = :user2) OR (m.sender = :user2 AND m.receiver = :user1)')
            ->andWhere('m.date > :date')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('date', ($date==null?new \DateTime('2000-01-01'):$date))
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Message[] Returns an array of Message objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
