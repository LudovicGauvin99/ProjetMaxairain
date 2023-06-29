<?php

namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        // Création de trois utilisateurs
        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setUsername('user' . $i);
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'password123'));

            $manager->persist($user);
        }

        $manager->flush();
    }
}