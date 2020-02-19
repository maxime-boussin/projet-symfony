<?php

namespace App\Controller;

use App\Entity\Excursion;
use App\Entity\Site;
use App\Form\ExcursionPostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ExcursionController extends AbstractController
{
    /**
     * @Route("/excursions/create", name="excursion")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createExcursion(Request $request)
    {
        $excursion = new Excursion();
        $excursion->setParticipantLimit(10);

        $user = $this->getUser();


        /** @noinspection PhpParamsInspection */
        $excursion->setOrganizer($user);


        $form = $this->createForm(ExcursionPostType::class, $excursion);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
//            $excursion = $form->getData();
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

            return $this->redirectToRoute('app_excursions');
        }

        return $this->render('excursions/create.html.twig', [
            'createExcursionForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/excursions/details/{id}", name="excursions_details")
     */
    public function detailsExcursion(Request $request, EntityManagerInterface $em, $id): Response
    {
        $excursion = $em->getRepository(Excursion::class)->find($id);
        if ($excursion != null) {
            $site = $excursion->getSite();
            $place = $excursion->getPlace();
            $city = $place->getCity();
            return $this->render('excursions/details.html.twig', [
                'excursion' => $excursion,
                'site' => $site,
                'place' => $place,
                'city' => $city
            ]);
        }

        return $this->redirectToRoute('app_excursions');
    }
}
