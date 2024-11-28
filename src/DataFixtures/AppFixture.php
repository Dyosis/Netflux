<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $movie = new Movie();
        $movie->setTitle('Mimir');
        $movie->setDirector('Moi');
        $movie->setReleaseDate(new \DateTime());

        $manager->persist($movie);
        $manager->flush();
    }
}
