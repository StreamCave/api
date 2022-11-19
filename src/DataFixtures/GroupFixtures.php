<?php

namespace App\DataFixtures;

use App\Entity\CameraGroup;
use App\Entity\InfoGroup;
use App\Entity\MatchGroup;
use App\Entity\PollGroup;
use App\Entity\PopupGroup;
use App\Entity\TweetGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class GroupFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->setCameraGroup($manager);
        $this->setInfoGroup($manager);
        $this->setMatchGroup($manager);
        $this->setPollGroup($manager);
        $this->setPopupGroup($manager);
        $this->setTweetGroup($manager);
    }

    public function setCameraGroup(ObjectManager $manager): void
    {
        $group = new CameraGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Camera'));
        $group->setIdNinja(Uuid::v5(Uuid::v6(), 'Default Id Ninja'));
        $group->setName('Default Camera');
        $group->setUplayTag('Default Uplay Tag');
        $group->setPositionTop(0);
        $group->setPositionBottom(0);
        $group->setPositionLeft(0);
        $group->setPositionRight(0);

        $this->addReference('default-camera-group', $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function setInfoGroup(ObjectManager $manager): void
    {
        $group = new InfoGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Info'));
        $group->setTitre('Default Title');
        $group->setDescription('Default Description');
        $group->setTextScroll(['Default Text Scroll', 'Default Text Scroll 2', 'Default Text Scroll 3']);

        $this->addReference('default-info-group', $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function setMatchGroup(ObjectManager $manager): void
    {
        $group = new MatchGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Match'));
        $group->setTeamNameA('Alpha');
        $group->setTeamNameB('Beta');
        $group->setStartDate(new \DateTimeImmutable());

        $manager->persist($group);
        $manager->flush();
        $this->addReference('default-match-group', $group);

    }

    public function setPollGroup(ObjectManager $manager): void
    {
        $group = new PollGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Poll'));
        $group->setQuestion('Default Question');
        $group->setAnswers(['Default Answer', 'Default Answer 2', 'Default Answer 3']);
        $group->setGoodAnswer(['Default Answer 2']);

        $this->addReference('default-poll-group', $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function setPopupGroup(ObjectManager $manager): void
    {
        $group = new PopupGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Popup'));
        $group->setContent('Default Content');

        $this->addReference('default-popup-group', $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function setTweetGroup(ObjectManager $manager): void
    {
        $group = new TweetGroup();
        $group->setPseudo('Default Pseudo');
        $group->setAt('defaultat');
        $group->setMediaType('image');
        $group->setMediaUrl('https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png');
        $group->setContent('Default Content');

        $this->addReference('default-tweet-group', $group);

        $manager->persist($group);
        $manager->flush();
    }
}
