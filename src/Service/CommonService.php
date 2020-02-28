<?php

namespace App\Service;

use App\Entity\Excursion;
use App\Entity\User;
use App\Form\ExcursionListFormType;
use App\Repository\ExcursionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommonService {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getExcursionsList (Request $request)
    {

    }

    /**
     * @param $id
     * @param NotificationService $notif
     * @param User $user
     * @return Excursion
     * @throws Exception
     */
    public function subscribeExcursion ( $id, NotificationService $notif, User $user): string
    {
        $excursion = $this->em->getRepository(Excursion::class)->updateAndFind($id);
        if ($excursion != null) {
            if ($excursion->getState() != 0 &&
                count($excursion->getParticipants()) < $excursion->getParticipantLimit() &&
                !$excursion->getParticipants()->contains($user) &&
                $excursion->getLimitDate() > new \DateTime()
            ) {
                $excursion->addParticipant($user);
                $this->em->flush();
                $notif->init($excursion->getOrganizer(), sprintf('%s s\'est inscrit à votre sortie.', $user->getNickname()), 'subscribe', $excursion);
                return 'Souscription à ' . $excursion->getName() . ' effectuée.';
            }
        }
        return 'Souscription impossible.';
    }

    /**
     * @param $id
     * @param NotificationService $notif
     * @return Excursion
     * @throws Exception
     */
    public function unsubscribeExcursion ($id, NotificationService $notif, User $user): string
    {
        $excursion = $this->em->getRepository(Excursion::class)->updateAndFind($id);
        if($excursion != null){
            if($excursion->getState() != 0 &&
                $excursion->getParticipants()->contains($user) &&
                $excursion->getLimitDate() > new \DateTime()
            ){
                $excursion->removeParticipant($user);
                $this->em->flush();
                $notif->init($excursion->getOrganizer(), sprintf('%s s\'est désinscrit de votre sortie.', $user->getNickname()), 'unsubscribe', $excursion);
                return 'Désinscription à '.$excursion->getName().' effectuée.';
            }
        }
        return 'Désinscription impossible.';
    }

    public function publishExcursion ($id, User $user): string
    {
        $excursion = $this->em->getRepository(Excursion::class)->find($id);

        if (($excursion != null) && ($excursion->getOrganizer()->getId() == $user->getId())) {
            $excursion->setState(1);
            $this->em->flush();
            $this->em->getRepository(Excursion::class)->updateState($id);
            return 'Sortie publiée.';
        }

        return 'Sortie non publiée';
    }
}