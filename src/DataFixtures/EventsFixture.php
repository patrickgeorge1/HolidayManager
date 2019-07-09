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
        for ($i = 0; $i <= 10; $i ++) {
            $faker = Factory::create();
            $event = new Events();
            $event->setTitle($faker->title);
            $event->setStart(new \DateTime());
            $event->setEnd(new \DateTime());
            $manager->persist($event);
        }
        $manager->flush();
    }

}
