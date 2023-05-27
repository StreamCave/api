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
        $this->setTwitchPoll($manager);
        $this->setTwitchPrediction($manager);
    }

    private function setTopbar(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("topbar");
        $libWidget->setNameWidget("TopBar");
        $libWidget->setNameGroup("info");

        $this->addReference('lib-widget-topbar', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setBottombar(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("bottombar");
        $libWidget->setNameWidget("BottomBar");
        $libWidget->setNameGroup("info");

        $this->addReference('lib-widget-bottombar', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setVersus(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("versus");
        $libWidget->setNameWidget("Versus");
        $libWidget->setNameGroup("match");

        $this->addReference('lib-widget-versus', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setNextMatch(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("nextmatch");
        $libWidget->setNameWidget("NextMatch");
        $libWidget->setNameGroup("match");

        $this->addReference('lib-widget-nextmatch', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setPopup(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("popup");
        $libWidget->setNameWidget("Popup");
        $libWidget->setNameGroup("popup");

        $this->addReference('lib-widget-popup', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setPoll(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("poll");
        $libWidget->setNameWidget("Poll");
        $libWidget->setNameGroup("poll");

        $this->addReference('lib-widget-poll', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setCamera(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("cameras");
        $libWidget->setNameWidget("Cameras");
        $libWidget->setNameGroup("camera");

        $this->addReference('lib-widget-camera', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setBracket(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("bracket");
        $libWidget->setNameWidget("Bracket");
        $libWidget->setNameGroup("match");

        $this->addReference('lib-widget-bracket', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setPlanning(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("planning");
        $libWidget->setNameWidget("Planning");
        $libWidget->setNameGroup("match");

        $this->addReference('lib-widget-planning', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setTweet(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("Tweets");
        $libWidget->setNameWidget("Tweets");
        $libWidget->setNameGroup("tweet");

        $this->addReference('lib-widget-tweet', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setImage(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("image");
        $libWidget->setNameWidget("Image");
        $libWidget->setNameGroup("media");

        $this->addReference('lib-widget-image', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setVideo(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("video");
        $libWidget->setNameWidget("Video");
        $libWidget->setNameGroup("media");

        $this->addReference('lib-widget-video', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setBanMap(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("banmap");
        $libWidget->setNameWidget("BanMap");
        $libWidget->setNameGroup("banmap");

        $this->addReference('lib-widget-banmap', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setTimer(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("timer");
        $libWidget->setNameWidget("Timer");
        $libWidget->setNameGroup("info");

        $this->addReference('lib-widget-timer', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setBannerPub(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("bannerpub");
        $libWidget->setNameWidget("BannerPub");
        $libWidget->setNameGroup("pub");

        $this->addReference('lib-widget-bannerpub', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
    private function setVideoPub(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("videopub");
        $libWidget->setNameWidget("VideoPub");
        $libWidget->setNameGroup("pub");

        $this->addReference('lib-widget-videopub', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }

    // TWITCH
    private function setTwitchPoll(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("twitchpoll");
        $libWidget->setNameWidget("TwitchPoll");
        $libWidget->setNameGroup("twitch");

        $this->addReference('lib-widget-twitchpoll', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }

    private function setTwitchPrediction(ObjectManager $manager): void
    {
        $libWidget = new LibWidget();
        $libWidget->setUuid("twitchprediction");
        $libWidget->setNameWidget("TwitchPrediction");
        $libWidget->setNameGroup("twitch");

        $this->addReference('lib-widget-twitchprediction', $libWidget);

        $manager->persist($libWidget);
        $manager->flush();
    }
}
