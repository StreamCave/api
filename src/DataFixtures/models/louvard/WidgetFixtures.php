<?php

namespace App\DataFixtures\models\louvard;

use App\DataFixtures\BracketFixtures;
use App\DataFixtures\OverlayFixtures;
use App\Entity\Widget;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class WidgetFixtures extends Fixture implements DependentFixtureInterface
{
    private const MODEL = 'louvard-1';

    public function load(ObjectManager $manager): void
    {
//        $this->setTopBar($manager);
//        $this->setBottomBar($manager);
//        $this->setCameras($manager);
//        $this->setMatch($manager);
//        $this->setPoll($manager);
//        $this->setPopup($manager);
//        $this->setTweets($manager);
//        $this->setMaps($manager);
//        $this->setPlanning($manager);
//        $this->setBracket($manager);
//        $this->setTwitchPoll($manager);
//        $this->setTwitchPrediction($manager);
    }

    private function setTopBar(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'TopBar'));
        $widget->setName('TopBar');
        $widget->setDescription('Barre en haut de page.');
        $widget->setVisible(false);
        $widget->setInfoGroup($this->getReference('info-group-' . self::MODEL));
        $widget->addMatchGroup($this->getReference('match-group-' . self::MODEL));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

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
        $widget->setInfoGroup($this->getReference('info-group-' . self::MODEL));
        $widget->addMatchGroup($this->getReference('match-group-' . self::MODEL));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setMatch(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Match'));
        $widget->setName('Match');
        $widget->setDescription('Match.');
        $widget->setVisible(false);
        $widget->addMatchGroup($this->getReference('match-group-' . self::MODEL));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

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
        $widget->setPollGroup($this->getReference('poll-group-' . self::MODEL));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

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
        $widget->setPopupGroup($this->getReference('popup-group-' . self::MODEL));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

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
        $widget->setTweetGroup($this->getReference('tweet-group-' . self::MODEL));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

        $manager->persist($widget);
        $manager->flush();
    }
    private function setCameras(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Cameras'));
        $widget->setName('Cameras');
        $widget->setDescription('Cameras.');
        $widget->setVisible(false);
        $widget->addCameraGroup($this->getReference('camera-group-1' . self::MODEL));
        $widget->addCameraGroup($this->getReference('camera-group-2' . self::MODEL));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

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
        $widget->addMapGroup($this->getReference('map-group-' . self::MODEL .  '-bo3-border'));
        $widget->addMapGroup($this->getReference('map-group-' . self::MODEL .  '-bo3-oregon'));
        $widget->addMapGroup($this->getReference('map-group-' . self::MODEL .  '-bo3-kafe'));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

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
        $widget->addPlanningGroup($this->getReference('planning-group-' . self::MODEL .  '-Alpha-vs-Delta'));
        $widget->addPlanningGroup($this->getReference('planning-group-' . self::MODEL .  '-Beta-vs-Echo'));
        $widget->addPlanningGroup($this->getReference('planning-group-' . self::MODEL .  '-Charlie-vs-Foxtrot'));
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));

        $manager->persist($widget);
        $manager->flush();
    }

    private function setBracket(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Bracket'));
        $widget->setName('Bracket');
        $widget->setDescription('Bracket.');
        $widget->setVisible(false);
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));
        $widget->setBracket($this->getReference('bracket-louvard'));
        $manager->persist($widget);
        $manager->flush();
    }

    private function setTwitchPoll(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'TwitchPoll'));
        $widget->setName('TwitchPoll');
        $widget->setDescription('TwitchPoll.');
        $widget->setVisible(false);
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));
        $widget->setTwitchGroup($this->getReference('twitch-poll-' . self::MODEL));
        $manager->persist($widget);
        $manager->flush();
    }

    private function setTwitchPrediction(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'TwitchPrediction'));
        $widget->setName('TwitchPrediction');
        $widget->setDescription('TwitchPrediction.');
        $widget->setVisible(false);
        $widget->setOverlay($this->getReference('overlay-' . self::MODEL));
        $widget->setTwitchGroup($this->getReference('twitch-prediction-' . self::MODEL));
        $manager->persist($widget);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return array(
            GroupFixtures::class,
            OverlayFixtures::class,
            BracketFixtures::class,
        );
    }
}
