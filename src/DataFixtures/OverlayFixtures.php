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
        $this->setupOverlay2($manager);
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

    private function setupOverlay2(ObjectManager $manager): void
    {
        $overlay = new Overlay();
        $overlay->setUuid(Uuid::v5(Uuid::v6(), 'Default Overlay 2'));
        $overlay->setName('Default Overlay 2');
        $overlay->setModel($this->getReference('default-model'));
        $overlay->setUserOwner($this->getReference('default-admin-user-2'));
        $overlay->addUserAccess($this->getReference('default-admin-user'));
        $overlay->addUserAccess($this->getReference('default-user-Alpha'));
        $overlay->addUserAccess($this->getReference('default-user-Beta'));

        $this->addReference('default-overlay-2', $overlay);

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
