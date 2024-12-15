<?php

namespace App\DataFixtures;

use App\Entity\Cat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $catsData = [
            ['name' => 'Minty', 'shortDescription' => 'Meet our little princess, Mint! She doesn\'t mind playing or taking long morning walks in the fresh air.', 'age' => '2 months', 'isVaccinated' => false],
            ['name' => 'Mikasa', 'shortDescription' => 'You\'ll love him from the second you meet him! His energetic and fun personality.', 'age' => '6 months', 'isVaccinated' => true],
            ['name' => 'Tommy', 'shortDescription' => 'Meet Timmy!! Timmy will make a great little pet for you. He loves to be out in the grass and loves to be held tight.', 'age' => '1 year', 'isVaccinated' => true]
        ];

        foreach ($catsData as $data) {
            $cat = new Cat();
            $cat->setName($data['name'])
                ->setShortDescription($data['shortDescription'])
                ->setAge($data['age'])
                ->setIsVaccinated($data['isVaccinated']);

            $manager->persist($cat);
        }

        $manager->flush();
    }
}
