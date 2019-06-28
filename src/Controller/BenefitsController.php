<?php

namespace App\Controller;

use App\Entity\Benefits;
use App\Form\AddBenefitType;
use App\Form\GiveBenefitType;
use App\Repository\BenefitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BenefitsController extends AbstractController
{
    private $entityManager;
    private $benefitsRepository;

    public function __construct(EntityManagerInterface $entityManager, BenefitsRepository $benefitsRepository)
    {
        $this->entityManager = $entityManager;
        $this->benefitsRepository = $benefitsRepository;
    }



    /**
     * @Route("/admin/benefits/list", name="benefits_admin_list")
     */
    public function adminListBenefits(Request $request)
    {
        // daca e user il trimit pe ruta lui
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_USER") return $this->redirectToRoute("benefits_user_list");

        $benefits = $this -> benefitsRepository -> findAll();


        // crez forma pe baza clasei sale
        $form = $this->createForm(AddBenefitType::class);

        // permit formei sa ia informatiile din requesturi
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            // benefit tine informatia transmisa prin form, si este deja o clasa pt ca am mapat forma la o clasa
            /** @var $benefit Users */  // ajut php storm sa stie ca e clasa cu get si set
            $benefit = $form->getData();

            $this -> entityManager->persist($benefit);
            $this -> entityManager->flush();
            return $this->redirectToRoute("benefits_admin_list");
        }


        return $this->render('benefits/listBenefitsAdmin.html.twig', [
            'benefits' => $benefits,
            'user_display' => $this->getUser()->getFirstName(),
            'profile' => $this->getUser()->getId(),
            'addBenefit' => $form->createView(),
            'person' => $this->getUser()
        ]);
    }



    /**
     * @Route("/user/benefits/list", name="benefits_user_list")
     */
    public function listBenefits(Request $request)
    {
        // daca e admin il trimit pe ruta lui
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_ADMIN") return $this->redirectToRoute("benefits_admin_list");

        $benefits = $this -> getUser() -> getBenefits();
        return $this->render('benefits/listBenefits.html.twig', [
            'benefits' => $benefits,
            'user_display' => $this->getUser()->getFirstName(),
            'profile' => $this->getUser()->getId(),
            'person' => $this->getUser()
        ]);
    }


    /**
     * @Route("/admin/benefits/give", name="benefits_admin_give")
     */
    public function giveBenefits(Request $request)
    {

        // daca e admin il trimit pe ruta lui
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_USER") return $this->redirectToRoute("benefits_user_list");


        $form = $this->createForm(GiveBenefitType::class);

        $form->handleRequest($request);
        $benefits = $this -> getUser() -> getBenefits();

        if($form->isSubmitted() && $form->isValid()) {

            // info va un array de obj {benefits, user}
            $info = $form->getData();
            $benefit = $info["name"];
            $user = $info["users"];
            $benefit->addUser($user);


            $this -> entityManager->persist($benefit);
            $this -> entityManager->flush();
            return $this -> redirectToRoute("benefits_admin_give");
        }
        return $this->render('benefits/giveBenefits.html.twig', [
            'giveBenefitForm' => $form->createView(),
            'benefits' => $benefits,
            'user_display' => $this->getUser()->getFirstName(),
            'profile' => $this->getUser()->getId(),
            'person' => $this->getUser()
        ]);




    }
}
