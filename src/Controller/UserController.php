<?php

namespace App\Controller;

use App\Entity\PrivateGroup;
use App\Entity\Site;
use App\Entity\User;
use App\Form\AddGroupMemberFormType;
use App\Form\PrivateGroupFormType;
use App\Form\ProfileFormType;
use App\Form\RecoverPasswordFormType;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

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
        }
        else{
            $this->addFlash(
                'danger',
                'Utilisateur introuvable.'
            );
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
                    $this->addFlash(
                        'success',
                        'Mot de passe changé avec succès.'
                    );
                    return $this->redirectToRoute('app_home');
                }
                else{
                    $this->addFlash(
                        'danger',
                        'Mot de passe inchangé.'
                    );
                }
            }
            return $this->render('user/resetPassword.html.twig', [
                'resetForm' => $form->createView(),
            ]);
        }
        else{
            $this->addFlash(
                'danger',
                'Lien incorrect.'
            );
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
            $this->addFlash(
                'success',
                'Groupe créé avec succès.'
            );
            return $this->redirectToRoute('app_list_private_group');
        }
        return $this->render('user/createPrivateGroup.html.twig', [
            'createPrivateGroupForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/group/list", name="app_list_private_group")
     * @IsGranted("ROLE_USER")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function listPrivateGroup(EntityManagerInterface $em): Response
    {
        $privateGroups = $em->getRepository(PrivateGroup::class)->findBy(['groupMaster' => $this->getUser()]);
        return $this->render('user/privateGroupList.html.twig', [
            'privateGroups' => $privateGroups
        ]);
    }

    /**
     * @Route("/profile/group/member/add/{id}", name="app_add_member_private_group")
     * @IsGranted("ROLE_USER")
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function addMemberToGroup(EntityManagerInterface $em, Request $request, $id): Response
    {
        $privateGroup = $em->getRepository(PrivateGroup::class)->find($id);
        $form = $this->createForm(AddGroupMemberFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('email')->getData() != null){
                $user = $em->getRepository(User::class)->findOneBy(['email' => $form->get('email')->getData()]);
                if ($user != null) {
                    if ($user != $this->getUser()){
                        if (!$privateGroup->getGroupMember()->contains($user)) {
                            $privateGroup->addGroupMember($user);
                            $em->flush();
                            $this->addFlash(
                                'success',
                                'Membre ajouté avec succès.'
                            );
                            return $this->redirectToRoute('app_list_member_private_group',['id' => $id]);
                        } else {
                            $this->addFlash(
                                'danger',
                                'Membre déjà dans le groupe.'
                            );
                        }
                    }
                    else {
                        $this->addFlash(
                            'danger',
                            'Vous ne pouvez pas vous ajouter à votre propre groupe.'
                        );
                    }
                } else {
                    $this->addFlash(
                        'danger',
                        'Utilisateur introuvable'
                    );
                }
            }
            if ($form->get('nickname')->getData() != null){
                $user = $em->getRepository(User::class)->findOneBy(['nickname' => $form->get('nickname')->getData()]);
                if ($user != null) {
                    if ($user != $this->getUser()){
                        if (!$privateGroup->getGroupMember()->contains($user)) {
                            $privateGroup->addGroupMember($user);
                            $em->flush();
                            $this->addFlash(
                                'success',
                                'Membre ajouté avec succès.'
                            );
                            return $this->redirectToRoute('app_list_member_private_group', ['id' => $id]);
                        }
                        else {
                            $this->addFlash(
                                'danger',
                                'Membre déjà dans le groupe.'
                            );
                        }
                    } else {
                        $this->addFlash(
                            'danger',
                            'Vous ne pouvez pas vous ajouter à votre propre groupe.'
                        );
                    }
                } else {
                    $this->addFlash(
                        'danger',
                        'Utilisateur introuvable'
                    );
                }
            }
        }
        $allUsers = $em->getRepository(User::class)->findAll();
        unset($allUsers[array_search($this->getUser(),$allUsers)]);
        return $this->render('user/addMemberGroup.html.twig', [
            'addMemberGroupForm' => $form->createView(),
            'privateGroup' => $privateGroup,
            'allUsers' => $allUsers
        ]);
    }

    /**
     * @Route("/profile/group/member/list/{id}", name="app_list_member_private_group")
     * @IsGranted("ROLE_USER")
     * @param EntityManagerInterface $em
     * @param $id
     * @return Response
     */
    public function listMemberGroup(EntityManagerInterface $em, $id): Response
    {
        $privateGroup = $em->getRepository(PrivateGroup::class)->find($id);
        $members = $privateGroup->getGroupMember();
        return $this->render('user/listGroupMember.html.twig', [
            'privateGroup' => $privateGroup,
            'members' => $members
        ]);
    }

    /**
     * @Route("/profile/group/member/delete/{groupId}/{userId}", name="app_delete_member_private_group")
     * @IsGranted("ROLE_USER")
     * @param EntityManagerInterface $em
     * @param $userId
     * @param $groupId
     * @return Response
     */
    public function deleteMemberGroup(EntityManagerInterface $em, $userId, $groupId): Response
    {
        $member = $em->getRepository(User::class)->find($userId);
        $em->getRepository(PrivateGroup::class)->find($groupId)->removeGroupMember($member);
        $em->flush();
        return $this->redirectToRoute('app_list_member_private_group', ['id' => $groupId]);
    }

    /**
     * @Route("/profile/group/delete/{groupId}", name="app_delete_private_group")
     * @IsGranted("ROLE_USER")
     * @param EntityManagerInterface $em
     * @param $groupId
     * @return Response
     */
    public function deleteGroup(EntityManagerInterface $em, $groupId): Response
    {
        $group = $em->getRepository(PrivateGroup::class)->find($groupId);
        $em->getRepository(User::class)->find($this->getUser()->getId())->removePrivateGroup($group);
        $em->flush();
        return $this->redirectToRoute('app_list_private_group');
    }

    /**
     * @Route("/session/refresh", name="app_check_activity")
     * @IsGranted("ROLE_USER")
     * @param EntityManagerInterface $em
     * @return Response
     * @throws \Exception
     */
    public function checkActivity(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $user->setLastActivity(new \DateTime());
        $em->flush();
        return new Response('Ok');
    }
}
