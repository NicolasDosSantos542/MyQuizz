<?php

namespace App\Controller;

use App\Entity\History;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\HistoryRepository;
use App\Repository\QuestionRepository;
use App\Repository\reponseRepository;

use App\Service\Reponse\ReponseUserService;
use ArrayObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
    #[Route('/quiz/{id}/{question}/{nbrQuestion}', name: 'quiz')]
    public function index($id, $question, $nbrQuestion, QuestionRepository $repoQ, reponseRepository $repoRep,
                          ReponseUserService $reponseUserService, HistoryRepository $reposH, ManagerRegistry $manager)
    {
        $questions = $repoQ->findByIdCategorie($id);

        if ($nbrQuestion < count($questions) - 1) {

            $lastResponse = $_GET["lastResponse"];
            $reponses = $repoRep->findBy(["idQuestion" => $question + 1]);

            $reponseUserService->addResponse($lastResponse, $question);
            if ($question > 9) {
                $nbrQuestion = $nbrQuestion + 1;
            } else {
                $nbrQuestion = $question;
            }

            if ($reposH->getRequest()->cookies->get('idGuest') === null) {
                $reposH->setCookieGuest($reposH->getRequest()->cookies->get('PHPSESSID'));
            }
            shuffle($reponses);
            return $this->render('quiz/index.html.twig', [
                'quiz' => $questions[$nbrQuestion],
                'reponses' => $reponses,
                'nbrQuestion' => $nbrQuestion,
            ]);

        } else {

            $lastResponse = $_GET["lastResponse"];

            $reponseUserService->addResponse($lastResponse, $question);

            $answers = $reponseUserService->getResponses();

            $reponseDone = new ArrayObject();
            $score = new ArrayObject();
            $score["nbrQuestion"] = count($questions);
            $score["result"] = 0;
            $nbr = 0;

            foreach ($answers as $answer) {

                $rep = $repoRep->findBy(["id" => $answer["idReponse"]]);

                $reponseExpected = $repoRep->findValueRepExpected($rep[0]->getIdQuestion()->getId());

                if ($rep[0]->getReponseExpected()) {
                    $score["result"]++;
                }
                $reponseDone[$nbr]["rep"] = $rep;
                $reponseDone[$nbr]["reponseExpected"] = $reponseExpected;
                $nbr++;
            }

            $questions[0]->getIdCategorie()->getId();

            $history = new History();
            $history->setIdCategorie($questions[0]->getIdCategorie()->getId());
            if ($this->getUser()) {
                $history->setIdUser($this->getUser()->getId());
            } else {
                $history->setIdGuest($reposH->getCookieGuest());
            }

            $history->setCompletedAt(new \DateTime());
            $history->setNote($score["result"]);

            $mr = $manager->getManager();
            $mr->persist($history);
            $mr->flush();

            return $this->render('quiz/showResult.html.twig', [
                'quiz' => $questions[0],
                'reponseDone' => $reponseDone,
                'score' => $score
            ]);
        }
    }

    #[Route('/result/{id}/{question}/{nbrQuestion}', name: 'showResult')]
    public function showResult($questions, $question, ReponseUserService $reponseUserService, HistoryRepository $reposH)
    {
        //ReponseUserService::getSession();
        $lastResponse = $_GET["lastResponse"];
        $reponseUserService->addResponse($lastResponse, $question);

        return $this->render('category/showResult.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }
}