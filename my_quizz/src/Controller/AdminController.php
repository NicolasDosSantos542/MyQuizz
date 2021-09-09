<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\EditUserAdminType;
use App\Repository\CategoryRepository;
use App\Repository\HistoryRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/utilisateurs', name: 'utilisateurs')]
    public function usersList(UsersRepository $users)
    {
        $howMany = count($users->findAll());

        return $this->render('admin/utilisateurs.html.twig', [
            'users' => $users->findAll(),
            'howMany' => $howMany
        ]);
    }

    #[Route('/admin/utilisateurs/modifier/{id}', name: 'modifier_utilisateur')]
    public function editUser(Users $user, Request $request)
    {
        $form = $this->createForm(EditUserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles($_POST['role']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('admin_');
        }

        return $this->render('admin/edituser.html.twig', [
            'userForm' => $form->createView(),
        ]);
    }

    #[Route('/admin/utilisateurs/delete/{id}', name: 'delete_utilisateur')]
    public function deleteUser(Users $user, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin_');
    }

    #[Route('/admin/quizz', name: 'admin_quizz')]
    public function quizzList(CategoryRepository $category, HistoryRepository $history): Response
    {
        $quizz = $category->findAll();
        foreach ($quizz as $categorie) {
            $count = count($history->selectByCategory($categorie->getID()));

            $categorie->setCount($count);
        }

        return $this->render('admin/quizzList.html.twig', [
            'categories' => $quizz,
            'historys' => $history->findAll(),
        ]);
    }

    #[Route('/admin/utilisateurs/validate/{id}/{from}', name: 'send_validation')]
    public function sendValidationEmail(MailerInterface $mailer, UsersRepository $users, $id, $from)
    {
        $user = $users->findOneBy(['id' => $id]);
        $emailing = new MailerController();
        $emailing->sendEmail($user, $mailer, 'mailer/validation.html.twig');
        if($from =="uniqueUser") {
            return $this->redirectToRoute('user_stats', ["userId"=>$id]);
        }elseif($from == "utilisateurs"){
            return $this->redirectToRoute('utilisateurs');

        }else{
            return $this->redirectToRoute('utilisateurs');

        }

    }

}
