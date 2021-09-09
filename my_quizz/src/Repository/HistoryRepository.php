<?php

namespace App\Repository;

use App\Entity\History;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method History|null find($id, $lockMode = null, $lockVersion = null)
 * @method History|null findOneBy(array $criteria, array $orderBy = null)
 * @method History[]    findAll()
 * @method History[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryRepository extends ServiceEntityRepository
{
    private $request;
    private $response;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, History::class);
        $this->response = new Response(
            Response::HTTP_OK,
        );
        $this->request = Request::createFromGlobals();
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setCookieGuest($phpSessId)
    {
        $this->response->headers->setCookie(Cookie::create('idGuest', $phpSessId));
        $this->response->send();
    }

    public function getCookieGuest()
    {
        return $this->request->cookies->get('idGuest');
    }

    public function countByCategory($value)
    {

        return $this->createQueryBuilder('c')
            ->select('count(c.idUser)')
            ->andWhere('c.idCategorie = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function selectByCategory($value)
    {

        return $this->createQueryBuilder('c')
            ->select('c.idUser')
            ->andWhere('c.idCategorie = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();
    }

    public function findUsersIds($val)
    {
        return $this->createQueryBuilder('h')
            ->select('h.idUser')
            ->andWhere('h.idCategorie =:val')
            ->setParameter('val', $val)
            ->getQuery()
            ->getResult();
    }

    public function findStatsByUser($val, $str)
    {
        return $this->createQueryBuilder('h')
            ->select('h.completedAt', 'h.idCategorie', 'h.note')
            ->andWhere('h.idUser =:val')
            ->andWhere('h.completedAt > :date')
            ->setParameter('val', $val)
            ->setParameter('date', new \DateTime($str))
            ->getQuery()
            ->getResult();
    }

    public function countStatsByUser($val, $str)
    {
        return $this->createQueryBuilder('h')
            ->select('count(h.completedAt)', 'sum(h.note)')
            ->andWhere('h.idUser =:val')
            ->andWhere('h.completedAt > :date')
            ->setParameter('val', $val)
            ->setParameter('date', new \DateTime($str))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastTimeUsed($val)
    {
        return $this->createQueryBuilder('h')
            ->select('h.completedAt')
            ->andWhere('h.idCategorie =:val')
            ->setParameter('val', $val)
            ->getQuery()
            ->getFirstResult();
    }

    public function countQuizzByUser($val)
    {
        return $this->createQueryBuilder('h')
            ->select('count(h.idCategorie)')
            ->andWhere('h.idCategorie = :val')
            ->setParameter('val', $val)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function countQuizzByUniqueUser($val)
    {
        return $this->createQueryBuilder('h')
            ->select('count(h.idUser)')
            ->andWhere('h.idCategorie = :val')
            ->groupBy('h.idUser')
            ->setParameter('val', $val)
            ->getQuery()
            ->getResult();
    }
    public function findNotesByQuizz($val)
    {
        return $this->createQueryBuilder('h')
            ->select('h.note')
            ->andWhere('h.idCategorie = :val')
            ->setParameter('val', $val)
            ->getQuery()
            ->getResult();

    }
}
