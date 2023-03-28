<?php

namespace App\DataFixtures;

use App\Entity\Brackets;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class BracketFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->setupBracket($manager);

        $manager->flush();
    }

    private function setupBracket(ObjectManager $manager): void
    {
        $bracket = new Brackets();
        $bracket->setUuid(Uuid::v4());
        $bracket->setName('Rainbow Six Siege');
        $bracket->setGame('r6');
        $bracket->setVisible(true);
        $bracket->setOverlayId($this->getReference('overlay-louvard')->getUuid());
        $bracket->setBracket([
            "1" => [
                "1" => [
                    "name" => "Team 1",
                    "score" => 0
                ],
                "2" => [
                    "name" => "Team 2",
                    "score" => 0
                ]
            ],
            "2" => [
                "1" => [
                    "name" => "Team 3",
                    "score" => 0
                ],
                "2" => [
                    "name" => "Team 4",
                    "score" => 0
                ]
            ]
        ]);
        $this->addReference('bracket-louvard', $bracket);
        $manager->persist($bracket);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            OverlayFixtures::class,
        ];
    }
}
