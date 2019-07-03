<?php

namespace App\DataFixtures;


use App\Entity\Demands;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormRegistry;


class UserFixture extends Fixture
{
    public function number() {
        $number = "";
        for ($i = 0; $i < 6; $i++) {
            $number = $number.mt_rand(0, 9);
        }
        return $number;
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


    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i <= 50; $i++) {
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
            $demand->setDate($randomDate);
            $demand->setDuration(rand ( 1 , 5 ));
        }


        $manager->flush();
    }



}
