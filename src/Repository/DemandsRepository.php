<?php

namespace App\Repository;

use App\Entity\Demands;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Demands|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demands|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demands[]    findAll()
 * @method Demands[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Demands::class);
    }

    // /**
    //  * @return Demands[] Returns an array of Demands objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Demands
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
