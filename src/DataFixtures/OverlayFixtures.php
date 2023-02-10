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
        $this->setupOverlay1($manager);
        $this->setupLouvard($manager);
    }

    private function setupOverlay1(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid(Uuid::v5(Uuid::v6(), 'Default Overlay'));
        $overlay->setName('Default Overlay');
        $overlay->setModel($this->getReference('default-model'));
        $overlay->setUserOwner($this->getReference('default-admin-user'));
        $overlay->addUserAccess($this->getReference('default-admin-user-2'));
        $overlay->addUserAccess($this->getReference('default-user-Alpha'));
        $overlay->addUserAccess($this->getReference('default-user-Beta'));

        $this->addReference('default-overlay', $overlay);

        $manager->persist($overlay);
        $manager->flush();
    }

    private function setupLouvard(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid(Uuid::v5(Uuid::v6(), 'Louvard'));
        $overlay->setName('Louvard');
        $overlay->setModel($this->getReference('model-louvard'));
        $overlay->setUserOwner($this->getReference('default-admin-user-2'));
        $overlay->addUserAccess($this->getReference('default-admin-user'));
        $overlay->addUserAccess($this->getReference('default-user-Alpha'));
        $overlay->addUserAccess($this->getReference('default-user-Beta'));

        $this->addReference('overlay-louvard', $overlay);

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
