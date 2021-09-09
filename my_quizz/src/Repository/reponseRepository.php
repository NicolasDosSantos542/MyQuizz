<?php


namespace App\Repository;

use App\Entity\Reponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class reponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reponse::class);
    }

    public function findValueRepExpected($idQuestion)
    {
        return $this->findBy(array('idQuestion' => $idQuestion, 'reponseExpected' => true));
    }
}