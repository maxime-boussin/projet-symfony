<?php

namespace App\Controller;

use App\Entity\Excursion;
use App\Entity\Site;
use App\Entity\User;
use App\Form\ProfileFormType;
use App\Form\ExcursionListFormType;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Validator\Constraints\Date;

class CommonController extends AbstractController
{
    /**
     * @Route("/excursions", name="app_excursions")
     * @IsGranted("ROLE_USER")
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
            $dateNow = new \DateTime();
            $excursions = $em->getRepository(Excursion::class)->nativeFindByFilters(
                $this->getUser()->getId(),
                1,
                null,
                $dateNow->add(new \DateInterval("P10Y")),
                $dateNow->sub(new \DateInterval("P10Y")),
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
}
