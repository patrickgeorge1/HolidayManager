<?php

namespace App\Controller;

use Phpml\SupportVectorMachine\Kernel;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Classification\SVC;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MlController extends AbstractController
{
    /**
     * @Route("/ml", name="ml_train")
     */
    public function train()
    {

        $samples = [[1], [2], [3], [4], [5], [6], [7], [2], [3], [4], [5]];
        $labels = ['0', '0', '0', '0', '0', '1', '1', '0', '0', '1', '1'];

        $classifier = new SVC(
            Kernel::LINEAR, // $kernel
            1.0,            // $cost
            3,              // $degree
            null,           // $gamma
            0.0,            // $coef0
            0.001,          // $tolerance
            100,            // $cacheSize
            true,           // $shrinking
            true            // $probabilityEstimates, set to true
        );
        $classifier->train($samples, $labels);


        return new Response(json_encode($classifier->predictProbability([3])));
    }
}
