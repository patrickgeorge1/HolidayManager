<?php

namespace App\DataFixtures;

use App\Entity\Events;
use App\Entity\Messages;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class EventsFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i <= 10; $i ++) {

            $event = new Events();
            $event->setTitle($faker->word);
            $event->setStart(new \DateTime());
            $event->setEnd(new \DateTime());
            $manager->persist($event);
        }
        $manager->flush();
    }

}
