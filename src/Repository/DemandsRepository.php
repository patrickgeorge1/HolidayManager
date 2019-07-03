<?php

namespace App\Repository;

use App\Entity\Demands;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @method Demands|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demands|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demands[]    findAll()
 * @method Demands[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandsRepository extends ServiceEntityRepository
{

    public function __construct(RegistryInterface $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Demands::class);
    }

    // /**
    //  * @return Demands[] Returns an array of Demands objects
    //  */

    public function findAllGreaterThanYear(string $year) : array
    {
        $customDate = "01-01-".$year;
        $year = \DateTime::createFromFormat('d-m-Y', $customDate);
        $qb = $this-> createQueryBuilder('d');
        $qb->where('d.date >= :date')
        ->setParameter('date',$year->format('Y-m-d'));
        $result = $qb->getQuery()
            ->getResult();
                return $result;
    }


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
