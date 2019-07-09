<?php

namespace App\DataFixtures;

use App\Entity\Benefits;
use App\Entity\Messages;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class BenefitsFixtures extends Fixture implements DependentFixtureInterface
{


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
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $faker = Factory::create();

            $benefit = new Benefits();
            $benefit->setName($faker->title);
            $benefit->setDescription($faker->text(50));
            $benefit->addUser($this->getReference(UserFixture::USER_REFERENCE.mt_rand(0,10)));
            $benefit->addUser($this->getReference(UserFixture::USER_REFERENCE.mt_rand(0,10)));

            $manager->persist($benefit);

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
