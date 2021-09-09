<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if ($user) {
            $user->setlastLog(new \DateTime());
            $manager->persist($user);
            $manager->flush();
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);

    }
}
