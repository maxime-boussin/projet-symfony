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

    public function getContacts(User $user, $new=null)
    {
        $contacts = $this->em->getRepository(Message::class)->findContacts($user);
        if($new != null){
            $contact = $this->em->getRepository(User::class)->find($new);
            if($contact instanceof User){
                if (($key = array_search($contact, $contacts)) !== false)
                    unset($contacts[$key]);
                array_unshift($contacts, $contact);
            }
        }
        return $contacts;
    }

    public function getLastConversation(User $user)
    {
        $lastUser = $this->em->getRepository(Message::class)->getLastUser($user);
        return $this->getConversation($user->getId(), $lastUser->getId());
    }

    public function getConversation(int $userId, int $contactId, \DateTime $date=null)
    {
        $user = $this->em->getRepository(User::class)->find($userId);
        $contact = $this->em->getRepository(User::class)->find($contactId);
        if($user instanceof User && $contact instanceof User){
            $conversation =  $this->em->getRepository(Message::class)->getConversation($user, $contact, $date);
            $this->setConversationSeen($user, $conversation);
            return $conversation;
        }
        else
            throw new \Exception('User not found.');
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

    private function setConversationSeen(User $user, $conversation )
    {
        foreach($conversation as $message){
            if($message->getReceiver() === $user){
                $message->setSeen(true);
            }
            $this->em->persist($message);
        }
        $this->em->flush();
        return $conversation;
    }
}