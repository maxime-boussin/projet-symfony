<?php

namespace App\Service;

use App\Entity\Excursion;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class MessengerService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getContacts(User $user)
    {
        return $this->em->getRepository(Message::class)->findContacts($user);
    }

    public function getLastConversation(User $user)
    {
        $lastUser = $this->em->getRepository(Message::class)->getLastUser($user);
        return $this->getConversation($user, $lastUser);
    }

    public function getConversation(User $user, User $contact)
    {
        return $this->em->getRepository(Message::class)->getConversation($user, $contact);
    }

    /**
     * @param int $senderId
     * @param int $receiverId
     * @param string $content
     * @throws \Exception
     */
    public function sendMessage(int $senderId, int $receiverId, string $content)
    {
        $sender = $this->em->getRepository(User::class)->find($senderId);
        $receiver = $this->em->getRepository(User::class)->find($receiverId);
        if($sender instanceof User && $receiver instanceof User && strlen($content) > 0){
            $message = (new Message())
                ->setDate(new \DateTime())
                ->setSeen(false)
                ->setContent($content)
                ->setSender($sender)
                ->setReceiver($receiver);
            $this->em->persist($message);
            $this->em->flush();
        }
        else{
            throw new \Exception("Invalid values");
        }
    }
}