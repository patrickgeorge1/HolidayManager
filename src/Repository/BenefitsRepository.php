<?php

namespace App\Repository;

use App\Entity\Benefits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Benefits|null find($id, $lockMode = null, $lockVersion = null)
 * @method Benefits|null findOneBy(array $criteria, array $orderBy = null)
 * @method Benefits[]    findAll()
 * @method Benefits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BenefitsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Benefits::class);
    }

    // /**
    //  * @return Benefits[] Returns an array of Benefits objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Benefits
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
