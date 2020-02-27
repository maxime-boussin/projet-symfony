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
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\DateTime;
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
     * @Route("/messenger/new/{user}", name="app_messenger_new")
     * @IsGranted("ROLE_USER")
     * @param MessengerService $service
     * @param int $user
     * @return Response
     * @throws \Exception
     */
    public function newContact(MessengerService $service, int $user): Response
    {
        $contacts = $service->getContacts($this->getUser(), $user);
        if(count($contacts)>0){
            $lastConversation = $service->getConversation($this->getUser()->getId(), $user);
            return $this->render('main/messenger.html.twig', [
                'contacts' => $contacts,
                'messages' => $lastConversation
            ]);
        }
        else{
            $this->addFlash(
                'danger',
                'Utilisateur inconnu.'
            );
            $this->redirectToRoute('app_home');
        }
    }

    /**
     * @Route("/messenger/send", name="app_messenge_send")
     * @IsGranted("ROLE_USER")
     * @param MessengerService $service
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function sendMessenge(MessengerService $service, Request $request): Response
    {
        $service->sendMessage($this->getUser()->getId(), $request->request->get('contact'), $request->request->get('content'));
        return new Response($request->request->get('contact').': '.$request->request->get('content'));
    }

    /**
     * @Route("/messenger/get", name="app_messenge_get")
     * @IsGranted("ROLE_USER")
     * @param MessengerService $service
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getConversation(MessengerService $service, Request $request): JsonResponse
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new PropertyNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer, new DateTimeNormalizer()]);
        $date = ($request->request->get('date') == null ? null : new \DateTime($request->request->get('date')));
        $messages = $service->getConversation($this->getUser()->getId(), $request->request->get('contact'), $date);
        $json = $serializer->normalize($messages, null, ['groups' => ['read']]);
        return new JsonResponse($json);
    }
}