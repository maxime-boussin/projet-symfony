<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\CsvUserFormType;
use App\Form\ProfileFormType;
use App\Form\RegistrationFormType;
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
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/import", name="app_admin_import")
     * @IsGranted("ROLE_USER")
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
                                ->setPassword($passwordEncoder->encodePassword($user, $line[4]))
                                ->setSite($em->getRepository(Site::class)->findOneBy(['name' => $line[5]]))
                                ->setNickname($line[6]);
                            $em->persist($user);
                        }
                    }
                    $em->flush();
                }
                else{
                    //TODO: Afficher erreur format
                }
            }
        }
        return $this->render('admin/importUser.html.twig', [
            'importForm' => $form->createView(),
        ]);
    }
}
