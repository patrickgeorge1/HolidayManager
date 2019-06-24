<?php

namespace App\DataFixtures;


use App\Entity\User;
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


    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i <= 12; $i++) {
            $user = new User();
            $user->setEmail("user".$i."@gmail.com");
            $user->setFirstName("user_first".$i.mt_rand(7, 33));
            $user->setLastName("user_last".$i.mt_rand(33, 77));
            $user->setPhone("+407".$this->number());
            $user->setPassword("test");
            $manager->persist($user);
        }


        $manager->flush();
    }
}
