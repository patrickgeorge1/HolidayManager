<?php

namespace App\Controller;

use App\Entity\Demands;
use App\Repository\DemandsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Classification\SVC;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MlController extends AbstractController
{

    private $demandsRepository;
    public function __construct(DemandsRepository $demandsRepository)
    {
        $this -> demandsRepository = $demandsRepository;
    }


    /**
     * @Route ("/populate")
     */
    public function populateDB(EntityManagerInterface $entityManager, UserRepository $userRepository) {
        for ($i = 0; $i <= 1000; $i++) {
//            $user = new User();
//            $user->setEmail("user".$i."@gmail.com");
//            $user->setRoles("ROLE_USER");
//            $user->setFirstName("user_first".$i.mt_rand(7, 33));
//            $user->setLastName("user_last".$i.mt_rand(33, 77));
//            $user->setPhone("+407".$this->number());
//            $user->setPassword("$2y$13\$gOW.OQV3SR1elCAU6rHRF.2DQMT7sFy6KFPEGrnMn0jYr7Io4.DIe");
//            $manager->persist($user);
            $demand = new Demands();
            $demand->setName($this->generateRandomString());
            $id = rand ( 200 , 209 );
            $user = $userRepository->findOneBy(['id' => $id]);
            $demand->setEmployee($user);
            $demand->setStatus(1);
            //Generate a timestamp using mt_rand.
            $timestamp = mt_rand(1, time());
            $randomDate = date("Y-m-d", $timestamp);
            $demand->setDate(\DateTime::createFromFormat("Y-m-d", $randomDate));
            $demand->setDuration(rand ( 1 , 5 ));
            $entityManager->persist($demand);
        }

        $entityManager->flush();
        return new Response("lal");
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    /**
     * @Route("/ml", name="ml_train")
     */
    public function train()
    {

        $samples = [["07.07"], ["02.03"], ["07.03"], ["07.07"], ["01.02"], ["01.02"], ["07.07"], ["08.11"], ["10.12"], ["07.07"], ["07.20"], ["10.25"]];
        $labels = ['1', '0', '1', '1', '0', '1', '1', '1', '1', '1', '1', '0'];

        $classifier = new SVC(
            Kernel::LINEAR, // $kernel
            100.0,            // $cost
            3,              // $degree
            null,           // $gamma
            0.0,            // $coef0
            0.001,          // $tolerance
            100,            // $cacheSize
            true,           // $shrinking
            true            // $probabilityEstimates, set to true
        );
        $classifier->train($samples, $labels);

        $dt = strtotime('06/22/2009');
        $day = date("m", $dt);
//        $day = date("d", $dt);
//        dd(($classifier->predictProbability(["07.07"]))[0]);  // predict of no
//        dd(($classifier->predictProbability(["07.07"]))[1]);  // predict of yes
        //return new Response($day);
        return new Response(json_encode($classifier->predictProbability(["10.25"])));
    }

}
