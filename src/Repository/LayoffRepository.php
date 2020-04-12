<?php

namespace App\Repository;

use App\Entity\Layoff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Layoff|null find($id, $lockMode = null, $lockVersion = null)
 * @method Layoff|null findOneBy(array $criteria, array $orderBy = null)
 * @method Layoff[]    findAll()
 * @method Layoff[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LayoffRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Layoff::class);
    }

    // /**
    //  * @return Layoff[] Returns an array of Layoff objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Layoff
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
