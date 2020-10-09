<?php

namespace App\DataFixtures;

use App\Entity\Commune;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $normandie =  new Commune();
        $imgNormandie = new Media();

        $normandie->setNom("Normandie")
            ->setLon(45645)
            ->setLat(45645)
            ->setCodePostal('76000')
            ->setCode('76')
            ->setCodeDepartement('Departement')
            ->setCodeRegion('codeRegion');
        $manager->persist($normandie);
        $imgNormandie->setVideo('https://www.photomaville.com/wp-content/uploads/img-tourisme-et-bien-etre-en-normandie-pour-les-velocyclistes.jpg')
            ->setCommune($normandie);
        $manager->persist($imgNormandie);
        // create une commune !
        for ($i = 0; $i < 20; $i++) {
            $faker = Faker\Factory::create('FR-fr');
            $commune = new Commune();
            $commune->setNom($faker->state)
                ->setLon($faker->numberBetween(10000,20000))
                ->setLat($faker->numberBetween(10000,20000))
                ->setCodePostal($faker->numberBetween(00000,90000))
                ->setCodeDepartement($faker->numberBetween(01,99))
                ->setCode($faker->numberBetween(10000,20000))
                ->setCodeRegion($faker->state);
            $manager->persist($commune);

            $video = new Media();
            $video->setVideo($faker->imageUrl(640,480,'city'))
                ->setCommune($commune);
            $manager->persist($video);
        }

        $manager->flush();
    }
}
