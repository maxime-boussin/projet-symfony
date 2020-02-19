<?php

namespace App\Controller;

use App\Entity\Excursion;
use App\Form\ExcursionPostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ExcursionController extends AbstractController
{
    /**
     * @Route("/excursion/create", name="excursion")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createExcursion(Request $request){
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

        return $this->render('excursions/create_excursion.html.twig', [
            'createExcursionForm' => $form->createView()
        ]);
    }
}
