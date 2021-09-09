<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\Users;
use App\Form\EditUserPasswordType;
use App\Form\EditUserType;
use App\Form\EditUserUsernameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfilController extends AbstractController
{
    #[Route('/profile', name: 'profile_')]
    public function index(): Response
    {
        $user = $this->getUser()->getId();
        $me = $this->getUser();
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
            'user' => $user,
            'me' => $me,
        ]);
    }

    #[Route('/profile/mail', name: 'profile_mail')]
    public function editMail(Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('security_logout');
        }

        return $this->render('profil/mail.html.twig', [
            'controller_name' => 'ProfilController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/username', name: 'profile_username')]
    public function editUsername(Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditUserUsernameType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('profile_');
        }

        return $this->render('profil/username.html.twig', [
            'controller_name' => 'ProfilController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/password', name: 'profile_password')]
    public function editPassword(EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $this->getUser();

        if (isset($_POST['ancien_password']) && isset($_POST['new_password']) && isset($_POST['conf_new_password'])) {
            $old_pwd = $_POST['ancien_password'];
            $newPass = $_POST['new_password'];
            $confNewPass = $_POST['conf_new_password'];

            $checkPass = $encoder->isPasswordValid($user, $old_pwd);
            if ($checkPass === true && $newPass === $confNewPass) {
                $hash = $encoder->encodePassword($user, $newPass);
                $user->setPassword($hash);
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('security_logout');
            }
            return $this->render('profil/password.html.twig', [
                'controller_name' => 'ProfilController',
                "error" => "L'ancien mot de passe n'est pas correct ou les nouveau mots de passe ne sont pas identique !",
            ]);
        }
        return $this->render('profil/password.html.twig', [
            'controller_name' => 'ProfilController',
            'error' => '',
        ]);
    }

    #[Route('/profile/delete/{id}', name: 'profile_delete')]
    public function deleteUser(Users $user, Request $request)
    {
        $deleteUser = $user->getId();
        $currentUser = $this->getUser()->getId();

        if ($deleteUser === $currentUser) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }
        else
        {
            return $this->redirectToRoute('profile_');
        }
    }

    #[Route('/profile/quiz/create', name: 'quizCreate')]
    public function createQuiz(): Response
    {
        if(!isset($_POST["nbrQuestion"])) {
            return $this->render('/profil/quizCreate.html.twig');
        } else if ($_POST["nbrQuestion"] === "10") {
            return $this->redirectToRoute('quizCreateTen');
        } else if ($_POST["nbrQuestion"] === "20") {
            return $this->redirectToRoute('quizCreateTwenty');
        }
        return $this->render('/profil/quizCreate.html.twig');
    }

    #[Route('/profile/quiz/createTen', name: 'quizCreateTen')]
    public function createQuizzTen(Categorie $category = null, EntityManagerInterface $manager): Response
    {
        if(isset($_POST["categorie"])) {

            if(!$category) {
                $category = new Categorie();
            }

            $isValid = true;
            $categorie = $_POST["categorie"];
            $questions = [];
            for ($i = 0; $i < 10 ;$i++) {
                array_push($questions, $_POST["question".$i]);
            }

            $reponses = [];
            for ($i = 0; $i < 30 ;$i++) {
                array_push($reponses, $_POST["reponse".$i]);
            }

            // if something is empty don't send
            foreach ($questions as $questionArr) {
                if(empty($questionArr)) {
                    $isValid = false;
                    //return $this->redirectToRoute('quizCreateTen');
                }
            }

            foreach ($reponses as $reponse) {
                if(empty($reponse)) {
                    $isValid = false;
                    //return $this->redirectToRoute('quizCreateTen');
                }
            }

            //if ($isValid) {
                $category->setName($categorie);
                $manager->persist($category);

                for($i = 0; $i < 10; $i++) {
                    $question = new Question();
                    $question->setQuestion($questions[$i]);
                    $question->setIdCategorie($category);
                    $manager->persist($question);
                    $manager->flush();

                    for($j = $i*3; $j < $i*3+3; $j++) {
                        $reponse = new Reponse();
                        if(!($j%3)){
                            $reponse->setReponseExpected(true);
                        }
                        else{
                            $reponse->setReponseExpected(false);
                        }
                        $reponse->setReponse($reponses[$j]);
                        $reponse->setIdQuestion($question);
                        $manager->persist($reponse);
                        $manager->flush();
                    }
                //}
            }
        } else {

        }

        return $this->render('/profil/quizCreateTen.html.twig');
    }

    #[Route('/profile/quiz/createTwenty', name: 'quizCreateTwenty')]
    public function createQuizzTwenty(Categorie $category = null, EntityManagerInterface $manager): Response
    {
        if(isset($_POST["categorie"])) {

            if(!$category) {
                $category = new Categorie();
            }

            $isValid = true;
            $categorie = $_POST["categorie"];
            $questions = [];
            for ($i = 0; $i < 20 ;$i++) {
                array_push($questions, $_POST["question".$i]);
            }

            $reponses = [];
            for ($i = 0; $i < 60 ;$i++) {
                array_push($reponses, $_POST["reponse".$i]);
            }

            // if something is empty don't send
            foreach ($questions as $questionArr) {
                if(empty($questionArr)) {
                    $isValid = false;
                    //return $this->redirectToRoute('quizCreateTen');
                }
            }

            foreach ($reponses as $reponse) {
                if(empty($reponse)) {
                    $isValid = false;
                    //return $this->redirectToRoute('quizCreateTen');
                }
            }

            //if ($isValid) {
            $category->setName($categorie);
            $manager->persist($category);

            for($i = 0; $i < 20; $i++) {
                $question = new Question();
                $question->setQuestion($questions[$i]);
                $question->setIdCategorie($category);
                $manager->persist($question);
                $manager->flush();

                for($j = $i*3; $j < $i*3+3; $j++) {
                    $reponse = new Reponse();
                    if(!($j%3)){
                        $reponse->setReponseExpected(true);
                    }
                    else{
                        $reponse->setReponseExpected(false);
                    }
                    $reponse->setReponse($reponses[$j]);
                    $reponse->setIdQuestion($question);
                    $manager->persist($reponse);
                    $manager->flush();
                }
                //}
            }
        } else {

        }

        return $this->render('/profil/quizCreateTwenty.html.twig');
    }
}
