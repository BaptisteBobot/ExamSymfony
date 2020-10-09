<?php


namespace App\DataFixtures;


use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;

class UserFixtures extends Fixture
{

    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user2 = new User();
        $user2->setMail('bonjour2@gmail.com')
            ->setPassword($this->passwordEncoder->encodePassword($user2, 'test'))
            ->setRoles(['ROLE_USER']);
        for ($i = 0; $i < 20; $i++) {
            $faker = Faker\Factory::create('FR-fr');
            $password = $faker->password;
            $user = new User();
            $user->setMail($faker->email)
                ->setPassword($this->passwordEncoder->encodePassword($user, $password))
                ->setRoles(["ROLE_USER"]);
            $manager->persist($user);
        }
        $manager->persist($user2);
        $manager->flush();
    }
}