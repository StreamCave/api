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
        $this->setCameras($manager);
    }

    public function setTopBar(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'TopBar'));
        $widget->setName('TopBar');
        $widget->setDescription('Barre de navigation en haut de page.');
        $widget->setVisible(false);
        $widget->setInfoGroup($this->getReference('default-info-group'));
        $widget->setModel($this->getReference('default-model'));

        $manager->persist($widget);
        $manager->flush();
    }

    public function setBottomBar(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'BottomBar'));
        $widget->setName('BottomBar');
        $widget->setDescription('Barre de navigation en bas de page.');
        $widget->setVisible(false);
        $widget->setInfoGroup($this->getReference('default-info-group'));
        $widget->setModel($this->getReference('default-model'));

        $manager->persist($widget);
        $manager->flush();
    }

    public function setNextMatch(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'NextMatch'));
        $widget->setName('NextMatch');
        $widget->setDescription('Prochain match.');
        $widget->setVisible(false);
        $widget->setMatchGroup($this->getReference('default-match-group'));
        $widget->setModel($this->getReference('default-model'));

        $manager->persist($widget);
        $manager->flush();
    }

    public function setCurrentMatch(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'CurrentMatch'));
        $widget->setName('CurrentMatch');
        $widget->setDescription('Match en cours.');
        $widget->setVisible(false);
        $widget->setMatchGroup($this->getReference('default-match-group'));
        $widget->setModel($this->getReference('default-model'));

        $manager->persist($widget);
        $manager->flush();
    }

    public function setPoll(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Poll'));
        $widget->setName('Poll');
        $widget->setDescription('Sondage.');
        $widget->setVisible(false);
        $widget->setPollGroup($this->getReference('default-poll-group'));
        $widget->setModel($this->getReference('default-model'));

        $manager->persist($widget);
        $manager->flush();
    }

    public function setPopup(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Popup'));
        $widget->setName('Popup');
        $widget->setDescription('Popup.');
        $widget->setVisible(false);
        $widget->setPopupGroup($this->getReference('default-popup-group'));
        $widget->setModel($this->getReference('default-model'));

        $manager->persist($widget);
        $manager->flush();
    }

    public function setTweets(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Tweets'));
        $widget->setName('Tweets');
        $widget->setDescription('Tweets.');
        $widget->setVisible(false);
        $widget->setTweetGroup($this->getReference('default-tweet-group'));
        $widget->setModel($this->getReference('default-model'));

        $manager->persist($widget);
        $manager->flush();
    }

    public function setCameras(ObjectManager $manager): void
    {
        $widget = new Widget();
        $widget->setUuid(Uuid::v5(Uuid::v6(), 'Cameras'));
        $widget->setName('Cameras');
        $widget->setDescription('Cameras.');
        $widget->setVisible(false);
        $widget->setCameraGroup($this->getReference('default-camera-group'));
        $widget->setModel($this->getReference('default-model'));

        $manager->persist($widget);
        $manager->flush();
    }


    public function getDependencies()
    {
        return array(
            GroupFixtures::class,
            ModelFixtures::class,
        );
    }
}
