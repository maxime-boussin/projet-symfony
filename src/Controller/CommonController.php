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

class CommonController extends AbstractController
{
    /**
     * @Route("/excursions", name="app_excursions")
     * @IsGranted("ROLE_USER")
     */
    public function listExcursions(EntityManagerInterface $em): Response
    {
        $excursions = $em->getRepository(Excursion::class)->findAll();
        $form = $this->createForm(ExcursionListFormType::class);
        return $this->render('excursions/list.html.twig', [
            'excursionListForm' => $form->createView(),
            'excursions' => $excursions,
        ]);
    }
}
