<?php

namespace App\DataFixtures;


use App\Entity\Demands;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserFixture extends Fixture
{
    private $passwordEncoder;
    public const USER_REFERENCE = 'user';

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this -> passwordEncoder = $passwordEncoder;
    }

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
        for ($i = 0; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("user".$i."@gmail.com");
            $user->setRoles("ROLE_USER");
            $user->setFirstName("user_first".$i.mt_rand(7, 33));
            $user->setLastName("user_last".$i.mt_rand(33, 77));
            $user->setPhone("+407".$this->number());
            $user->setPassword(
                $this -> passwordEncoder->encodePassword(
                    $user,
                    "testtest"
                )
            );
            $this->addReference(self::USER_REFERENCE.$i, $user);
            $manager->persist($user);
        }
        $manager->flush();
    }



}
