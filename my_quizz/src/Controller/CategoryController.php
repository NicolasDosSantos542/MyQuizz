<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\HistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use App\Repository\QuestionRepository;
use App\Repository\reponseRepository;
use App\Service\Reponse\ReponseUserService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @ORM\Entity
 * @ORM\Table(name="categorie")
 */
class CategoryController extends AbstractController
{
    #[Route('/category', name: 'category')]
    public function index(CategoryRepository $repos, ReponseUserService $reponseUserService)
    {
        $reponseUserService->removeResponses();
        $categories = $repos->findAll();
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories
        ]);
    }

    #[Route('/category/{id}', name: 'category_show')]

    public function showCategorie(QuestionRepository $repoQ,reponseRepository $repoRep, $id) {

        $questions = $repoQ->findByIdCategorie($id);
        $reponses = $repoRep->findBy(["idQuestion" => $questions[0]]);
        return $this->render('category/show.html.twig', [
            'quiz' => $questions[0],
            'reponses' => $reponses,
        ]);
    }
}
