<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Users;
use App\Repository\HistoryRepository;
use App\Repository\UsersRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{
    #[Route('/mailer', name: 'mailer')]
    public function index(): Response
    {
        return $this->render('mailer/validation.html.twig', [
            'controller_name' => 'MailerController',
            'expiration_date' => new \DateTime('+7 days'),
        ]);
    }

    #[Route('/email/{id}', name: 'email')]
    public function sendEmail(Users $user, MailerInterface $mailer, $template)
    {

        $email = (new TemplatedEmail())
            ->from('lessuhisalacreme@webac.tech')
            ->to($user->getMail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Bonjour, ' . $user->getUsername() . ' !')
            ->htmlTemplate($template)
            ->context([
                'user' => $user,
            ]);


        $mailer->send($email);


    }  public function sendEmailForQuizz(Users $user, MailerInterface $mailer, Categorie $category, $template)
    {

        $email = (new TemplatedEmail())
            ->from('lessuhisalacreme@webac.tech')
            ->to($user->getMail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Bonjour, ' . $user->getUsername() . ' !')
            ->htmlTemplate($template)
            ->context([
                'user' => $user,
                'category'=> $category
            ]);


        $mailer->send($email);


    }

    #[Route('/sendmail/{what}', name: 'sendmail')]
    public function sendEmailToMany(UsersRepository $users, MailerInterface $mailer, $what)
    {
        if ($what == 'recall') {
            $users = $users->findDoThingsLastMonth(new \DateTime('- 1 month'));
            foreach ($users as $user) {
                $this->sendEmail($user, $mailer, 'mailer/doThingsLastMonth.html.twig');
            }
        } elseif ($what == "doNoForget") {
            $users = $users->findNothingDoneLastMonth(new \DateTime('- 1 month'));

            foreach ($users as $user) {
                $this->sendEmail($user, $mailer, 'mailer/nothingDoneLastMonth.html.twig');
            }
        }
        return $this->redirectToRoute('utilisateurs');
    }

    #[Route('/sendquizzemail/{what}/{id}', name: 'send_quizz_email')]
    public function sendQuizzEmail(UsersRepository $users, HistoryRepository $history, Categorie $category, MailerInterface $mailer, $what, $id)
    {

        $categoryUsers = $history->findUsersIds($id);
        $allUsers = $users->findAll();
        $list = array();
        if ($what == 'noQuizzDone') {
            foreach ($categoryUsers as $categoryUser) {
                $user = $users->findOneBy(['id' => $categoryUser]);
                array_push($list, $user);
            }
            $newList = array();
            foreach ($allUsers as $user) {
                if (in_array($user, $list)) {
                    echo "in array <br/";

                } elseif (!in_array($user, $list)) {
                    array_push($newList, $user);
                }

            }
            foreach ($newList as $newListUser) {
                $this->sendEmailForQuizz($newListUser, $mailer, $category,'mailer/noQuizzDone.html.twig');
            }


        } elseif ($what == "quizzDone") {
            foreach ($categoryUsers as $categoryUser) {
                $user = $users->findOneBy(['id' => $categoryUser]);
                array_push($list, $user);
            }
            foreach ($list as $user) {
                $this->sendEmailForQuizz($user, $mailer, $category,'mailer/quizzDone.html.twig');
            }
        } else {
            return null;
        }
        return $this->redirectToRoute('admin_quizz');
    }

}
