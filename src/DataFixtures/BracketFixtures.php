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
//        $this->setupBracket($manager);

        $manager->flush();
    }

    private function setupBracket(ObjectManager $manager): void
    {
        $bracket = new Brackets();
        $bracket->setUuid(Uuid::v4());
        $bracket->setName('Rainbow Six Siege');
        $bracket->setGame('r6');
        $bracket->setVisible(true);
//        $bracket->setOverlayId($this->getReference('overlay-test')->getUuid());
        $bracket->setType("DOUBLE");
        // Set le bracket à partir d'un fichier json
        $bracket->setBracket([file_get_contents(__DIR__ . '/brackets/r6.json')]);
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
