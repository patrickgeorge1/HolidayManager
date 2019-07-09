<?php

namespace App\DataFixtures;

use App\Entity\Messages;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MessagesFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        for ($i = 1; $i <= 4; $i ++) {
            $faker = Factory::create();
            $message = new Messages();
            $message->setTitle($faker->title);
            $message->setBody($faker->jobTitle);
            $message->setAdmin($this->getReference(UserFixture::USER_REFERENCE.mt_rand(1, 4)));
            $manager->persist($message);
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
