<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\CsvUserFormType;
use App\Form\ProfileFormType;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/register", name="admin_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $em
     * @param NotificationService $notif
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em, NotificationService $notif): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setActive(true);
            $site = $em->getRepository(Site::class)->findOneBy(['name' => $user->getSite()->getName()]);
            $user->setSite($site === null ? $em->getRepository(Site::class)->find(1) : $site);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $notif->init($user, 'Bienvenue sur Sortir.com, vous pouvez dès maintenant créer une sortie !', 'welcome');
            $this->addFlash(
                'success',
                'Utilisateur enregistré avec succès.'
            );
        }

        return $this->render('admin/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/import", name="admin_import")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function import(Request $request,  UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createForm(CsvUserFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($form['file']->getData() != null){
                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $form['file']->getData();
                if($uploadedFile->guessExtension() == 'txt'){
                    $em = $this->getDoctrine()->getManager();
                    $data = explode("\n", str_replace("\r", '', file_get_contents($uploadedFile)));
                    foreach ($data as $i => $line){
                        $line = explode(",", $line);
                        if($i != 0 && count($line) == 7){
                            $user = new User();
                            $user->setFirstName($line[0])
                                ->setLastName($line[1])
                                ->setPhone($line[2])
                                ->setEmail($line[3])
                                ->setActive(true)
                                ->setPassword($passwordEncoder->encodePassword($user, $line[4]))
                                ->setSite($em->getRepository(Site::class)->findOneBy(['name' => $line[5]]))
                                ->setNickname($line[6]);
                            $em->persist($user);
                        }
                    }
                    $em->flush();
                    $this->addFlash(
                        'success',
                        'Importation réalisée avec succès.'
                    );
                }
                else{
                    $this->addFlash(
                        'danger',
                        'Format de fichier inconnu.'
                    );
                }
            }
        }
        return $this->render('admin/importUser.html.twig', [
            'importForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/users/{page}", name="admin_users")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param int $page
     * @return Response
     * @throws \Exception
     */
    public function userList(Request $request, EntityManagerInterface $em, int $page): Response
    {
        $users = $em->getRepository(User::class)->paginate($page, 5);

        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($users) / 5),
            'routeName' => 'admin_users',
            'routeParams' => array()
        );
        return $this->render('admin/listUser.html.twig', [
            'pagination' => $pagination,
            'users' => $users,
        ]);
    }

    /**
     * @Route("/admin/users/disable/{id}", name="admin_user_disable")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param int $id
     * @return Response
     */
    public function userDisable(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if($user instanceof User){
            $user->setActive(false);
            $em->flush($user);
            $this->addFlash(
                'success',
                'Utilisateur desactivé avec succès.'
            );
        }
        else{
            $this->addFlash(
                'danger',
                'Utilisatteur introuvable.'
            );
        }
        return $this->redirectToRoute('admin_users', ['page' => 1]);
    }

    /**
     * @Route("/admin/users/delete/{id}", name="admin_user_delete")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param int $id
     * @return Response
     */
    public function userDelete(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if($user instanceof User){
            $em->remove($user);
            $em->flush();
            $this->addFlash(
                'success',
                'Utilisateur supprimé avec succès.'
            );
        }
        else{
            $this->addFlash(
                'danger',
                'Utilisateur inconnu.'
            );
        }
        return $this->redirectToRoute('admin_users', ['page' => 1]);
    }
}
