<?php

namespace App\Controller;

use App\Entity\Demands;
use App\Entity\User;
use App\Repository\DemandsRepository;
use App\Repository\UserRepository;
use App\Service\MlService;
use Doctrine\ORM\EntityManagerInterface;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Classification\SVC;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MlController extends AbstractController
{

    private $demandsRepository;
    private $mlService;
    public function __construct(DemandsRepository $demandsRepository, MlService $mlService)
    {
        $this -> demandsRepository = $demandsRepository;
        $this->mlService =$mlService;
    }


    /**
     * @Route("/blahhh")
     */
    public function blahhhh(MlService $mlService) {
        $day = 1;
        $free = 1;
        return new Response($this -> mlService -> predictAjustment($day, $free));


    }



}
