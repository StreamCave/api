<?php

namespace App\DataFixtures\models\her6s;

use App\DataFixtures\OverlayFixtures;
use App\Entity\Widget;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class WidgetFixtures extends Fixture implements DependentFixtureInterface
{
    private const MODEL = 'her6s';

    public function load(ObjectManager $manager): void
    {
        $this->setTopBar($manager);
        $this->setCameras($manager);
    }

    private function setTopBar(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'TopBar'));
        $widget->setName('TopBar');
        $widget->setDescription('Barre en haut de page.');
        $widget->setVisible(false);
        $widget->setInfoGroup($this->getReference('info-group-' . self::MODEL));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setCameras(ObjectManager $manager): void
    {
        $players = ['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo'];
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Cameras'));
        $widget->setName('Cameras');
        $widget->setDescription('Cameras.');
        $widget->setVisible(false);
        $widget->addCameraGroup($this->getReference('camera-group-' . self::MODEL .  '-' . $players[0]));
        $widget->addCameraGroup($this->getReference('camera-group-' . self::MODEL .  '-' . $players[1]));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

        $manager->persist($widget);
        $manager->flush();
    }


    public function getDependencies(): array
    {
        return array(
            GroupFixtures::class,
            OverlayFixtures::class,
        );
    }
}
