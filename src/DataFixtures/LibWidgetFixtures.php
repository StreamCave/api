<?php

namespace App\DataFixtures;

use App\Entity\LibWidget;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class LibWidgetFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->setTopbar($manager);
        $this->setBottombar($manager);
        $this->setVersus($manager);
        $this->setNextMatch($manager);
        $this->setPopup($manager);
        $this->setPoll($manager);
        $this->setCamera($manager);
        $this->setBracket($manager);
        $this->setPlanning($manager);
        $this->setTweet($manager);
        $this->setImage($manager);
        $this->setVideo($manager);
        $this->setBanMap($manager);
        $this->setTimer($manager);
        $this->setBannerPub($manager);
        $this->setVideoPub($manager);
    }

    private function setTopbar(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "topbar"));
        $libWidget->setNameWidget("topbar");
        $libWidget->setNameGroup("info");

        $this->addReference('lib-widget-topbar', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setBottombar(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "bottombar"));
        $libWidget->setNameWidget("bottombar");
        $libWidget->setNameGroup("info");

        $this->addReference('lib-widget-bottombar', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setVersus(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "versus"));
        $libWidget->setNameWidget("versus");
        $libWidget->setNameGroup("match");

        $this->addReference('lib-widget-versus', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setNextMatch(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "nextmatch"));
        $libWidget->setNameWidget("nextmatch");
        $libWidget->setNameGroup("match");

        $this->addReference('lib-widget-nextmatch', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setPopup(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "popup"));
        $libWidget->setNameWidget("popup");
        $libWidget->setNameGroup("popup");

        $this->addReference('lib-widget-popup', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setPoll(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "poll"));
        $libWidget->setNameWidget("poll");
        $libWidget->setNameGroup("poll");

        $this->addReference('lib-widget-poll', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setCamera(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "camera"));
        $libWidget->setNameWidget("camera");
        $libWidget->setNameGroup("camera");

        $this->addReference('lib-widget-camera', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setBracket(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "bracket"));
        $libWidget->setNameWidget("bracket");
        $libWidget->setNameGroup("match");

        $this->addReference('lib-widget-bracket', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setPlanning(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "planning"));
        $libWidget->setNameWidget("planning");
        $libWidget->setNameGroup("match");

        $this->addReference('lib-widget-planning', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setTweet(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "tweet"));
        $libWidget->setNameWidget("tweet");
        $libWidget->setNameGroup("tweet");

        $this->addReference('lib-widget-tweet', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setImage(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "image"));
        $libWidget->setNameWidget("image");
        $libWidget->setNameGroup("media");

        $this->addReference('lib-widget-image', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setVideo(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "video"));
        $libWidget->setNameWidget("video");
        $libWidget->setNameGroup("media");

        $this->addReference('lib-widget-video', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setBanMap(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "banmap"));
        $libWidget->setNameWidget("banmap");
        $libWidget->setNameGroup("banmap");

        $this->addReference('lib-widget-banmap', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setTimer(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "timer"));
        $libWidget->setNameWidget("timer");
        $libWidget->setNameGroup("info");

        $this->addReference('lib-widget-timer', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setBannerPub(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "bannerpub"));
        $libWidget->setNameWidget("bannerpub");
        $libWidget->setNameGroup("pub");

        $this->addReference('lib-widget-bannerpub', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setVideoPub(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "videopub"));
        $libWidget->setNameWidget("videopub");
        $libWidget->setNameGroup("pub");

        $this->addReference('lib-widget-videopub', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }

    // TWITCH
    private function setTwitchPoll(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "twitchpoll"));
        $libWidget->setNameWidget("twitchpoll");
        $libWidget->setNameGroup("twitch");

        $this->addReference('lib-widget-twitchpoll', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }

    private function setTwitchPrediction(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid(Uuid::v5(Uuid::v6(), "twitchprediction"));
        $libWidget->setNameWidget("twitchprediction");
        $libWidget->setNameGroup("twitch");

        $this->addReference('lib-widget-twitchprediction', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
}
