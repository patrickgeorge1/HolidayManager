<?php

namespace App\DataFixtures;

use App\Entity\Demands;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DemandsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i <= 10; $i++) {
            $demand = new Demands();
            $demand->setName("demand".$i);
            $demand->setDuration(mt_rand(1,3));
            $demand->setStatus(1);
            $demand->setDate(new \DateTime());
            $demand->setEmployee($this->getReference(UserFixture::USER_REFERENCE.mt_rand(0,10)));

            $manager->persist($demand);
        }
        $manager->flush();
    }


    public function getDependencies()
    {
        return array(
            UserFixture::class,
        );
    }
}
