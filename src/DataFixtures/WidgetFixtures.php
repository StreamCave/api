<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Widget;
use Symfony\Component\Uid\Uuid;

class WidgetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->setTopBar($manager);
        $this->setBottomBar($manager);
        $this->setNextMatch($manager);
        $this->setCurrentMatch($manager);
        $this->setPoll($manager);
        $this->setPopup($manager);
        $this->setTweets($manager);
        $this->setCamerasTeamA($manager);
        $this->setCamerasTeamB($manager);
        $this->setMaps($manager);
        $this->setPlanning($manager);
    }

    private function setTopBar(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'TopBar'));
        $widget->setName('TopBar');
        $widget->setDescription('Barre en haut de page.');
        $widget->setVisible(false);
        $widget->setInfoGroup($this->getReference('info-group-louvard'));
        $widget->setMatchGroup($this->getReference('match-group-louvard'));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setBottomBar(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'BottomBar'));
        $widget->setName('BottomBar');
        $widget->setDescription('Barre en bas de page.');
        $widget->setVisible(false);
        $widget->setInfoGroup($this->getReference('info-group-louvard'));
        $widget->setMatchGroup($this->getReference('match-group-louvard'));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setNextMatch(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'NextMatch'));
        $widget->setName('NextMatch');
        $widget->setDescription('Prochain match.');
        $widget->setVisible(false);
        $widget->setMatchGroup($this->getReference('match-group-louvard'));
        $widget->setOverlay($this->getReference('overlay-louvard'));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setCurrentMatch(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'CurrentMatch'));
        $widget->setName('CurrentMatch');
        $widget->setDescription('Match en cours.');
        $widget->setVisible(false);
        $widget->setMatchGroup($this->getReference('match-group-louvard'));
        $widget->setOverlay($this->getReference('overlay-louvard'));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setPoll(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Poll'));
        $widget->setName('Poll');
        $widget->setDescription('Sondage.');
        $widget->setVisible(false);
        $widget->setPollGroup($this->getReference('poll-group-louvard'));
        $widget->setOverlay($this->getReference('overlay-louvard'));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setPopup(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Popup'));
        $widget->setName('Popup');
        $widget->setDescription('Popup.');
        $widget->setVisible(false);
        $widget->setPopupGroup($this->getReference('popup-group-louvard'));
        $widget->setOverlay($this->getReference('overlay-louvard'));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setTweets(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Tweets'));
        $widget->setName('Tweets');
        $widget->setDescription('Tweets.');
        $widget->setVisible(false);
        $widget->setTweetGroup($this->getReference('tweet-group-louvard'));
        $widget->setOverlay($this->getReference('overlay-louvard'));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setCamerasTeamA(ObjectManager $manager): void
    {
        $players = ['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo'];
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'CameraTeamA'));
        $widget->setName('CameraTeamA');
        $widget->setDescription('Camera de l\'équipe A.');
        $widget->setVisible(false);
        $widget->addCameraGroup($this->getReference('camera-group-louvard-' . $players[0]));
        $widget->addCameraGroup($this->getReference('camera-group-louvard-' . $players[1]));
        $widget->addCameraGroup($this->getReference('camera-group-louvard-' . $players[2]));
        $widget->addCameraGroup($this->getReference('camera-group-louvard-' . $players[3]));
        $widget->addCameraGroup($this->getReference('camera-group-louvard-' . $players[4]));
        $widget->setOverlay($this->getReference('overlay-louvard'));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setCamerasTeamB(ObjectManager $manager): void
    {
        $players = ['Foxtrot', 'Golf', 'Hotel', 'India', 'Juliett'];
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'CameraTeamB'));
        $widget->setName('CameraTeamB');
        $widget->setDescription('Camera de l\'équipe B.');
        $widget->setVisible(false);
        $widget->addCameraGroup($this->getReference('camera-group-louvard-' . $players[0]));
        $widget->addCameraGroup($this->getReference('camera-group-louvard-' . $players[1]));
        $widget->addCameraGroup($this->getReference('camera-group-louvard-' . $players[2]));
        $widget->addCameraGroup($this->getReference('camera-group-louvard-' . $players[3]));
        $widget->addCameraGroup($this->getReference('camera-group-louvard-' . $players[4]));
        $widget->setOverlay($this->getReference('overlay-louvard'));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setMaps(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Maps'));
        $widget->setName('Maps');
        $widget->setDescription('Cartes.');
        $widget->setVisible(false);
        $widget->addMapGroup($this->getReference('map-group-louvard-bo3-border'));
        $widget->addMapGroup($this->getReference('map-group-louvard-bo3-oregon'));
        $widget->addMapGroup($this->getReference('map-group-louvard-bo3-kafe'));
        $widget->setOverlay($this->getReference('overlay-louvard'));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setPlanning(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Planning'));
        $widget->setName('Planning');
        $widget->setDescription('Planning.');
        $widget->setVisible(false);
        $widget->addPlanningGroup($this->getReference('planning-group-louvard-Alpha-vs-Delta'));
        $widget->addPlanningGroup($this->getReference('planning-group-louvard-Beta-vs-Echo'));
        $widget->addPlanningGroup($this->getReference('planning-group-louvard-Charlie-vs-Foxtrot'));
        $widget->setOverlay($this->getReference('overlay-louvard'));

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
