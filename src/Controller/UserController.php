<?php

namespace App\Controller;

use App\Entity\PrivateGroup;
use App\Entity\Site;
use App\Entity\User;
use App\Form\PrivateGroupFormType;
use App\Form\ProfileFormType;
use App\Form\RecoverPasswordFormType;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="app_profile")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function udpateProfile(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('newPassword')->getData() !== null) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('newPassword')->getData()
                    )
                );
            }
            if ($form['avatar']->getData() != null) {
                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $form['avatar']->getData();
                $destination = $this->getParameter('kernel.project_dir') . '/public/uploads';
                $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $user->setAvatarPath($newFilename);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
        }
        // Faire la vue etc...
        return $this->render('user/profile.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/{username}", name="app_foreign_profile")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param string $username
     * @return Response
     */
    public function displayProfile(Request $request, EntityManagerInterface $em, string $username): Response
    {
        $profile = $em->getRepository(User::class)->findOneBy(['nickname' => $username]);
        if ($profile != null) {
            return $this->render('user/foreign-profile.html.twig', [
                'profile' => $profile,
            ]);
        } else {
            //TODO: Afficher "Not found profile" info
            return $this->redirectToRoute('app_excursions');
        }
    }

    /**
     * @Route("/recoverPassword", name="app_forgotten_password")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TokenGeneratorInterface $tokenGenerator
     * @return Response
     */
    public function forgottenPassword(Request $request, EntityManagerInterface $em, TokenGeneratorInterface $tokenGenerator)
    {
        $token = null;
        $form = $this->createForm(RecoverPasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form['email']->getData() != null) {
                $user = $em->getRepository(User::class)->findOneBy(['email' => $form['email']->getData()]);
                if ($user instanceof User) {
                    $token = $tokenGenerator->generateToken();
                    $user->setResetToken($token);
                    $em->flush();
                    $token = $request->getUri() . '/' . $token;
                }
            }
        }
        return $this->render('user/recoverPassword.html.twig', [
            'recoverForm' => $form->createView(),
            'token' => $token
        ]);
    }


    /**
     * @Route("/recoverPassword/{token}", name="app_reset_password")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param $token
     * @return Response
     */
    public function resetPassword(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, $token)
    {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);
        if ($user instanceof User) {
            $form = $this->createForm(ResetPasswordFormType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('newPassword')->getData() !== null) {
                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user,
                            $form->get('newPassword')->getData()
                        )
                    );
                    $user->setResetToken(null);
                    $em->flush();
                    //TODO ajouter un flash "Mot de passe changé avec succès."
                    return $this->redirectToRoute('app_home');
                } else {
                    //TODO erreur mot de passe inchangé
                }
            }
            return $this->render('user/resetPassword.html.twig', [
                'resetForm' => $form->createView(),
            ]);
        } else {
            //TODO erreur mauvais token
        }
    }

    /**
     * @Route("/profile/group/create", name="app_create_private_group")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @return Response
     */
    public function createPrivateGroup(Request $request): Response
    {
        $privateGroup = new PrivateGroup();
        $form = $this->createForm(PrivateGroupFormType::class, $privateGroup);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $privateGroup->setGroupMaster($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($privateGroup);
            $entityManager->flush();
            //TODO: Afficher un message success
            return $this->redirectToRoute('app_excursions');
        }
        return $this->render('user/createPrivateGroup.html.twig', [
            'createPrivateGroupForm' => $form->createView()
        ]);
    }
}
