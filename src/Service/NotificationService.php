<?php

namespace App\Service;

use App\Entity\Excursion;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function init(User $user, string $message, string $type, Excursion $concerned = null)
    {
        $notif = new Notification();
        $notif->setUser($user);
        $notif->setMessage($message);
        $notif->setSeen(false);
        $notif->setType($type);
        $notif->setDate(new \DateTime());
        if($concerned != null)
            $notif->setConcerned($concerned);
        $this->em->persist($notif);
        $this->em->flush();
    }

    public function seen(int $id)
    {
        $this->em->remove($this->em->getRepository(Notification::class)->find($id));
        $this->em->flush();
    }
}