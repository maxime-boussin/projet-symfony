<?php

namespace App\Controller;


use App\Entity\Cancellation;
use App\Entity\City;
use App\Entity\Excursion;
use App\Entity\Notification;
use App\Form\CancellationFormType;
use App\Form\CityFormType;
use App\Form\ExcursionListFormType;
use App\Form\ExcursionPostType;
use App\Repository\ExcursionRepository;
use App\Service\MessengerService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Sodium\add;

class MessageController extends AbstractController
{
    /**
     * @Route("/messenger", name="app_messenger")
     * @IsGranted("ROLE_USER")
     * @param MessengerService $service
     * @return Response
     */
    public function displayMessenger(MessengerService $service): Response
    {
        $lastConversation = $service->getLastConversation($this->getUser());
        $contacts = $service->getContacts($this->getUser());
        return $this->render('main/messenger.html.twig', [
            'contacts' => $contacts,
            'messages' => $lastConversation
        ]);
    }

    /**
     * @Route("/messenger/send", name="app_messenge_send")
     * @IsGranted("ROLE_USER")
     * @param MessengerService $service
     * @return Response
     * @throws \Exception
     */
    public function sendMessenge(MessengerService $service, Request $request): Response
    {
        $service->sendMessage($this->getUser()->getId(), $request->request->get('contact'), $request->request->get('content'));
        return new Response($request->request->get('contact').': '.$request->request->get('content'));
    }
}