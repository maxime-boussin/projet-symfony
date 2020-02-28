<?php

namespace App\Controller;


use App\Entity\Cancellation;
use App\Entity\City;
use App\Entity\Excursion;
use App\Form\CancellationFormType;
use App\Form\CityFormType;
use App\Form\ExcursionListFormType;
use App\Form\ExcursionPostType;
use App\Repository\ExcursionRepository;
use App\Service\CommonService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommonController extends AbstractController
{
    /**
     * @Route("/excursions", name="app_excursions")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * @throws Exception
     */
    public function listExcursions(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ExcursionListFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $excursions = $em->getRepository(Excursion::class)->nativeFindByFilters(
                $this->getUser()->getId(),
                $form->get('site')->getData()->getId(),
                $form->get('excursion_content')->getData(),
                $form->get('from_date')->getData(),
                $form->get('to_date')->getData(),
                $form->get('owned_excursions')->getData(),
                $form->get('subscribed_excursions')->getData(),
                $form->get('not_subscribed_excursions')->getData(),
                $form->get('past_excursions')->getData()
            );
        }
        else{
            $excursions = $em->getRepository(Excursion::class)->nativeFindByFilters(
                $this->getUser()->getId(),
                1,
                null,
                (new \DateTime())->sub(new \DateInterval("P5Y")),
                (new \DateTime())->add(new \DateInterval("P5Y")),
                true,
                true,
                true,
                true
            );
        }
        return $this->render('excursions/list.html.twig', [
            'excursionListForm' => $form->createView(),
            'excursions' => $excursions,
        ]);
    }

    /**
     * @Route("/subscribe/{id}", name="app_subscribe")
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param CommonService $commonService
     * @param NotificationService $notif
     * @return Response
     * @throws Exception
     */
    public function subscribeExcursions($id, CommonService $commonService, NotificationService $notif): Response
    {
        try {
            $message = $commonService->subscribeExcursion($id, $notif, $this->getUser());
            $this->addFlash(
                'success',
                sprintf($message)
            );
        } catch (Exception $exception) {
            $this->addFlash(
                'danger',
                'Souscription impossible.'
            );
        }
        return $this->redirectToRoute('app_excursions');
    }

    /**
     * @Route("/unsubscribe/{id}", name="app_unsubscribe")
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param NotificationService $notif
     * @param CommonService $commonService
     * @return Response
     * @throws Exception
     */
    public function unsubscribeExcursions($id, NotificationService $notif, CommonService $commonService): Response
    {
        try {
            $message = $commonService->unsubscribeExcursion($id, $notif, $this->getUser());
            $this->addFlash(
                'success',
                sprintf($message)
            );
        } catch (Exception $exception) {
            $this->addFlash(
                'danger',
                'Désinscription impossible.'
            );
        }
        return $this->redirectToRoute('app_excursions');
    }

    /**
     * @Route("/cancel/{id}", name="app_cancel_excursion")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param $id
     * @return Response
     */
    public function cancelExcursions(Request $request, EntityManagerInterface $em, $id): Response
    {
        $excursion = $em->getRepository(Excursion::class)->updateAndFind($id);
        if($excursion != null){
            if($excursion->getState() != 5 && ($excursion->getOrganizer() == $this->getUser() || in_array('ROLE_ADMIN', $this->getUser()->getRoles()))){
                $cancellation = new Cancellation();
                $form = $this->createForm(CancellationFormType::class, $cancellation);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $excursion->setState(5);
                    $cancellation->setExcursion($excursion);
                    $entityManager->persist($cancellation);
                    $entityManager->flush();
                    $this->addFlash(
                        'success',
                        'Sortie annulée avec succès.'
                    );
                }
                else{
                    return $this->render('excursions/cancel.html.twig', [
                        'cancellationForm' => $form->createView(),
                        'excursion' => $excursion,
                    ]);
                }
            }
        }
        return $this->redirectToRoute('app_excursions');
    }

    /**
     * @Route("/excursions/new", name="app_create_excursion")
     *
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function createExcursion(Request $request){
        $excursion = new Excursion();

        $user = $this->getUser();


        /** @noinspection PhpParamsInspection */
        $excursion->setOrganizer($user);
        $excursion->setVisibility(true);

        $form = $this->createForm(ExcursionPostType::class, $excursion);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $site = $form->get('site')->getData();
            $city = $form->get('place')->get('city')->getData();
            $place = $form->get('place')->getData();
            $excursion->setSite($site);
            $excursion->setState(0);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($city);
            $entityManager->persist($place);
            $entityManager->persist($excursion);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Sortie créée avec succès.'
            );

            return $this->redirectToRoute('app_excursions');
        }
        return $this->render('excursions/create.html.twig', [
            'createExcursionForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/excursions/details/{id}", name="app_details_excursions")
     * @IsGranted("ROLE_USER")
     */
    public function detailsExcursion(EntityManagerInterface $em, $id): Response
    {
        $excursion = $em->getRepository(Excursion::class)->find($id);
        if ($excursion != null) {
            $site = $excursion->getSite();
            $place = $excursion->getPlace();
            $city = $place->getCity();
            $isOwner = $excursion->getOrganizer()->getId() == $this->getUser()->getId();
            $nbParticipants = $excursion->getParticipants()->count();
            return $this->render('excursions/details.html.twig', [
                'excursion' => $excursion,
                'site' => $site,
                'place' => $place,
                'city' => $city,
                'is_owner' => $isOwner,
                'nb_participants' => $nbParticipants,
                'participants' => $excursion->getParticipants()
            ]);
        }

        return $this->redirectToRoute('app_excursions');
    }

    /**
     * @Route("/excursions/publish/{id}", name="app_publish_excursions")
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param CommonService $commonService
     * @return Response
     */
    public function publishExcursion($id, CommonService $commonService)
    {
        try {
            $message = $commonService->publishExcursion($id, $this->getUser());
            $this->addFlash(
                'success',
                $message
            );
            return $this->redirectToRoute('app_excursions');
        } catch (Exception $exception){
            $this->addFlash(
                'danger',
                'Sortie non publiée'
            );
        }
    }

    /**
     * @Route("/", name="app_home")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function home(EntityManagerInterface $em) {
        $rep = $em->getRepository(Excursion::class);
        $year = $rep->getNbYear();
        $month = $rep->getNbMonth();
        $top = $rep->getTopUser();
        $chart = $rep->getYearCut();
        $messages = $rep->getNbMessages();
        return $this->render("main/home.html.twig", [
            "nbYear" => $year,
            "nbMonth" => $month,
            "topUser" => $top,
            "chart" => $chart,
            "messages" => $messages
        ]);
    }


    /**
     * @Route("/aboutus", name="app_aboutus")
     */
    public function aboutus() {
        return $this->render("main/aboutus.html.twig");
    }

    /**
     * @Route("/city/create", name="app_create_city")
     * @param Request $request
     * @return Response
     */
    public function newCity(Request $request): Response {
        $city = new City();
        $form = $this->createForm(CityFormType::class, $city);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($city);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Ville créée avec succès.'
            );
            return $this->redirectToRoute('app_excursions');
        }
        $this->addFlash(
            'danger',
            'Création de la ville non aboutie'
        );

        return $this->render('main/city.html.twig', [
            'createCityForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/notifications/seen/{id}", name="app_notification_seen")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param int $id
     * @param NotificationService $notif
     * @return Response
     */
    public function notificationSeen(Request $request, int $id, NotificationService $notif):Response{
        $notif->seen($id);
        return new Response($id);
    }
}
