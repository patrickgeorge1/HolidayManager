<?php

namespace App\DataFixtures;

use App\Entity\Demands;
use App\Entity\Messages;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class DemandsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i <= 10; $i++) {

            $demand = new Demands();
            $demand->setName($faker->word);
            $demand->setDuration($faker->numberBetween(1, 5));
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
