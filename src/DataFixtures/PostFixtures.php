<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use function Sodium\add;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr-FR");

        for ($i=0; $i<20;$i++){
            $post = new Post();
            $post->setTitle($faker->words($faker->numberBetween(3,10),true ))
                ->setContent($faker->paragraphs($faker->numberBetween(2,4),true))
                ->setCreatedAt($faker->dateTimeBetween('-6 months'));
            $manager->persist($post);

        }

        $manager->flush();
    }
}
