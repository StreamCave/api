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
        $this->setupLouvard($manager);
        $this->setupLouvard2($manager);
        $this->setupLouvard3($manager);
        $this->setupFlowUp($manager);
        $this->setupHER6S($manager);
        $this->setupRoadToLan($manager);
    }

    private function setupLouvard(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid(Uuid::v5(Uuid::v6(), 'Louvard 1'));
        $overlay->setName('Louvard 1');
        $overlay->setModel($this->getReference('model-louvard'));
        $overlay->setUserOwner($this->getReference('default-admin-user-2'));
        $overlay->addUserAccess($this->getReference('default-admin-user'));
        $overlay->addUserAccess($this->getReference('default-user-Alpha'));
        $overlay->addUserAccess($this->getReference('default-user-Beta'));

        $this->addReference('overlay-louvard-1', $overlay);

        $manager->persist($overlay);
        $manager->flush();
    }

    private function setupLouvard2(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid(Uuid::v5(Uuid::v6(), 'Louvard 2'));
        $overlay->setName('Louvard 2');
        $overlay->setModel($this->getReference('model-louvard'));
        $overlay->setUserOwner($this->getReference('default-admin-user-2'));
        $overlay->addUserAccess($this->getReference('default-admin-user'));
        $overlay->addUserAccess($this->getReference('default-user-Alpha'));
        $overlay->addUserAccess($this->getReference('default-user-Beta'));

        $this->addReference('overlay-louvard-2', $overlay);

        $manager->persist($overlay);
        $manager->flush();
    }

    private function setupLouvard3(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid(Uuid::v5(Uuid::v6(), 'Louvard 3'));
        $overlay->setName('Louvard 3');
        $overlay->setModel($this->getReference('model-louvard'));
        $overlay->setUserOwner($this->getReference('default-admin-user-2'));
        $overlay->addUserAccess($this->getReference('default-admin-user'));
        $overlay->addUserAccess($this->getReference('default-user-Alpha'));
        $overlay->addUserAccess($this->getReference('default-user-Beta'));

        $this->addReference('overlay-louvard-3', $overlay);

        $manager->persist($overlay);
        $manager->flush();
    }

    private function setupFlowUp(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid(Uuid::v5(Uuid::v6(), 'FlowUp'));
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
        $overlay->setUuid(Uuid::v5(Uuid::v6(), 'HER6S'));
        $overlay->setName('HER6S');
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
        $overlay->setUuid(Uuid::v5(Uuid::v6(), 'RoadToLan'));
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
