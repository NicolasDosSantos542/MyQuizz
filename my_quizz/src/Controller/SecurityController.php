<?php

namespace App\Controller;
use App\Entity\Users;
use App\Form\RegistrationType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    #[Route("/inscription", name: "security_registration")]
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, MailerInterface $mailer) {

        $user = new Users();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash)
                 ->setVerifiedAt(NULL)
                 ->setCreatedAt(new \DateTime())
                 ->setRoles('ROLE_USER');

            $manager->persist($user);
            $manager->flush();

            $emailing = new MailerController();

            $emailing->sendEmail($user, $mailer, 'mailer/validation.html.twig');

            return $this->redirectToRoute('security_login');
        }
        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route("/login", name: "security_login")]
    public function login() {
        return $this->render('security/login.html.twig');
    }

    #[Route("/validation/{id}", name: "security_validator")]
    public function validateRegistration($id, UsersRepository $users, EntityManagerInterface $manager)
    {

        $user = $users->findOneBy(['id' => $id]);


        $user->setVerifiedAt(new \DateTime(),);

        $manager->persist($user);
        $manager->flush();

        return $this->render('mailer/email.html.twig', [
            'user'=> $user
        ]);
    }

    #[Route("/logout", name: "security_logout")]
    public function logout() {}
}
