<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\HistoryRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'stats')]
    public function index()
    {
        return $this->render('stats/index.html.twig', [
            'controller_name' => 'StatsController',
        ]);
    }

    #[Route('/admin/stats/user/{userId}', name: 'user_stats')]
    public function showUserStats(HistoryRepository $history, $userId, UsersRepository $users)
    {
        $user = $users->findOneBy(['id' => $userId]);
        $statsCounts = $this->searchForStatsCounts($history, $userId);

        return $this->render('stats/user.html.twig', [
            'user' => $user,
            'statsCounts' => $statsCounts,
        ]);
    }

    public function searchForStatsCounts(HistoryRepository $history, $userId)
    {
        $all = $history->countStatsByUser($userId, '-100 years');
        $year = $history->countStatsByUser($userId, '-1 year');
        $month = $history->countStatsByUser($userId, '-1 month');
        $week = $history->countStatsByUser($userId, '-1 week');
        $day = $history->countStatsByUser($userId, '-1 day');
        return [
            "year" => $year["1"],
            "month" => $month["1"],
            "week" => $week["1"],
            "day" => $day["1"],
            "all" => $all["1"],
            "yearNote" => $this->createAverage($year["2"] , $year["1"]),
            "monthNote" => $this->createAverage($month["2"] ,$month["1"]),
            "weekNote" => $this->createAverage($week["2"] , $week["1"]),
            "dayNote" => $this->createAverage($day["2"] , $day["1"]),
            "allNote" => $this->createAverage($all["2"] , $all["1"]),
        ];

    }
    public function createAverage($note, $nbr){
        if($nbr>0){
            return round($note/$nbr,2);
        }else{
            return "X";
        }


    }

    #[Route('/admin/stats/quizz/{id}', name: 'quizz_stats')]
    public function showQuizzStats(HistoryRepository $history,$id, CategoryRepository $category): Response
    {

        $quizz= $category->findOneBy(['id' => $id]);
        $lastTime= $history->findLastTimeUsed($id);
        $countTotal= $history->countQuizzByUser($id);
        $countbyUniqueUser= $history->countQuizzByUniqueUser($id);
        $howManyUsers= count($countbyUniqueUser);
        $totalUses=$countTotal["1"];
       $notes =  $this->notes($id, $history);


        return $this->render('stats/quizz.html.twig', [
            'category' => $quizz,
            'lastTime' => $lastTime,
            'howManuUsers'=> $howManyUsers,
            'totalUses' =>$totalUses,
            'notes'=>$notes
        ]);

    }

    public function notes($id, HistoryRepository $history){
       $req= $history->findNotesByQuizz($id);

       $notes= array();
        foreach($req as $value){
            array_push($notes, $value['note']);
        }
        if($notes) {
            $sum = array_sum($notes);
            $average = $this->createAverage($sum, count($notes));
            $best = max($notes);
            $worst = min($notes);

            return [
                "average" => $average,
                "max" => $best,
                "min" => $worst
            ];
        }else{
            return [
                "average" => "X",
                "max" => "X",
                "min" => "X"
            ];
        }
    }

}
