<?php

namespace App\Controller;

use App\Entity\Cancellation;
use App\Entity\Excursion;
use App\Form\CancellationFormType;
use App\Form\ExcursionListFormType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommonController extends AbstractController
{
    /**
     * @Route("/excursions/list", name="app_excursions")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * @throws \Exception
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

    /**
     * @Route("/subscribe/{id}", name="app_subscribe")
     * @IsGranted("ROLE_USER")
     * @param EntityManagerInterface $em
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function subscribeExcursions(EntityManagerInterface $em, $id): Response
    {
        $excursion = $em->getRepository(Excursion::class)->find($id);
        if($excursion != null){
            if($excursion->getState() != 0 &&
                count($excursion->getParticipants()) < $excursion->getParticipantLimit() &&
                !$excursion->getParticipants()->contains($this->getUser()) &&
                $excursion->getLimitDate() > new \DateTime()
            ){
                $excursion->addParticipant($this->getUser());
                $em->flush();
            }
            else{
                //TODO: Afficher message d'erreur
            }
        }
        return $this->redirectToRoute('app_excursions');
    }

    /**
     * @Route("/unsubscribe/{id}", name="app_unsubscribe")
     * @IsGranted("ROLE_USER")
     * @param EntityManagerInterface $em
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function unsubscribeExcursions(EntityManagerInterface $em, $id): Response
    {
        $excursion = $em->getRepository(Excursion::class)->find($id);
        if($excursion != null){
            if($excursion->getState() != 0 &&
                $excursion->getParticipants()->contains($this->getUser()) &&
                $excursion->getLimitDate() > new \DateTime()
            ){
                $excursion->removeParticipant($this->getUser());
                $em->flush();
            }
            else{
                //TODO: Afficher message d'erreur
            }
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
        $excursion = $em->getRepository(Excursion::class)->find($id);
        if($excursion != null){
            if($excursion->getState() != 0 && $excursion->getOrganizer() == $this->getUser()){
                $cancellation = new Cancellation();
                $form = $this->createForm(CancellationFormType::class, $cancellation);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $excursion->setState(-1);
                    $cancellation->setExcursion($excursion);
                    $entityManager->persist($cancellation);
                    $entityManager->flush();
                    //TODO: Afficher un message success
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
}
