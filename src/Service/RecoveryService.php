<?php


namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RecoveryService
{

    private $passwordEncoder;
    private $entityManager;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getRandomToken() {
        return bin2hex(random_bytes(64));
    }

    public function changePassword(User $user, string $newPassword) : void {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $newPassword));
        $user->setToken($this -> getRandomToken());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }


    public function giveToken(User $user, string $token) : void {
        $user -> setToken($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}