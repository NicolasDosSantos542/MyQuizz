<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\HistoryRepository;
use App\Repository\QuestionRepository;
use ArrayObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    #[Route('/history', name: 'history')]
    public function index(HistoryRepository $reposH, CategoryRepository $reposC, QuestionRepository $reposQ): Response
    {
        if($reposH->getCookieGuest() !== null) {

            $history = $reposH->findBy(["idGuest" => $reposH->getCookieGuest()]);

            $historyAll = new ArrayObject();
            $nbr = 0;

            if($this->getUser()) {

                $historyUser = $reposH->findBy(["idUser" => $this->getUser()->getId()]);
                foreach ($historyUser as $dataUser) {

                    $questions = $reposQ->findBy(["idCategorie" => $dataUser->getIdCategorie()]);
                    ;
                    $historyAll[$nbr]['categorie'] = $reposC->findBy(["id" => $dataUser->getIdCategorie()]);
                    $historyAll[$nbr]['history'] = $dataUser;
                    $historyAll[$nbr]['nbrQuestion'] = count($questions);
                    $nbr++;
                }
            }

            foreach ($history as $dataGuest) {
                $questions = $reposQ->findBy(["idCategorie" => $dataGuest->getIdCategorie()]);
                $historyAll[$nbr]['categorie'] = $reposC->findBy(["id" => $dataGuest->getIdCategorie()]);
                $historyAll[$nbr]['history'] = $dataGuest;
                $historyAll[$nbr]['nbrQuestion'] = count($questions);
                $nbr++;
            }
            
            return $this->render('history/index.html.twig', [
                'history' => $historyAll,
                'reponse' => 'Vous n\'avez pas d\'historique',
            ]);

        } else {
            return $this->render('history/index.html.twig', [
                'reponse' => 'Vous n\'avez pas d\'historique',
            ]);
        }

    }
}
