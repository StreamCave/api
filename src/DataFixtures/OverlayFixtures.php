<?php

namespace App\DataFixtures;

use App\Entity\Overlay;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class OverlayFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
//        $this->setupSixquatre($manager);
        $this->setupFlowUp($manager);
        $this->setupHER6S($manager);
        $this->setupRoadToLan($manager);
    }

    private function setupSixquatre(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid("sixquatre");
        $overlay->setName('Sixquatre');
        $overlay->setModel($this->getReference('model-her6s'));
        $overlay->setUserOwner($this->getReference('default-admin-user-2'));
        $overlay->addUserAccess($this->getReference('default-admin-user'));

        $this->addReference('overlay-sixquatre', $overlay);

        $manager->persist($overlay);
        $manager->flush();
    }

    private function setupFlowUp(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid('flowup');
        $overlay->setName('FlowUp');
        $overlay->setModel($this->getReference('model-flowup'));
        $overlay->setUserOwner($this->getReference('default-admin-user-2'));
        $overlay->addUserAccess($this->getReference('default-admin-user'));
        $overlay->addUserAccess($this->getReference('default-admin-user-3'));

        $this->addReference('overlay-flowup', $overlay);

        $manager->persist($overlay);
        $manager->flush();
    }

    private function setupHER6S(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid('sixquatre');
        $overlay->setName('Sixquatre');
        $overlay->setModel($this->getReference('model-her6s'));
        $overlay->setUserOwner($this->getReference('default-admin-user-2'));
        $overlay->addUserAccess($this->getReference('default-admin-user'));

        $this->addReference('overlay-her6s', $overlay);

        $manager->persist($overlay);
        $manager->flush();
    }

    private function setupRoadToLan(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid('roadtolan');
        $overlay->setName('RoadToLan');
        $overlay->setModel($this->getReference('model-roadtolan'));
        $overlay->setUserOwner($this->getReference('default-admin-user-2'));
        $overlay->addUserAccess($this->getReference('default-admin-user'));

        $this->addReference('overlay-roadtolan', $overlay);

        $manager->persist($overlay);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ModelFixtures::class,
            UserFixtures::class,
        ];
    }
}
