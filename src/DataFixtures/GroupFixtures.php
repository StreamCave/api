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
        $this->setCameraGroupAlpha($manager);
        $this->setCameraGroupBeta($manager);
        $this->setInfoGroup($manager);
        $this->setMatchGroup($manager);
        $this->setPollGroup($manager);
        $this->setPopupGroup($manager);
        $this->setTweetGroup($manager);
    }

    public function setCameraGroupAlpha(ObjectManager $manager): void
    {
        $group = new CameraGroup();
        $group->setName("Camera Alpha");
        $group->setVisible(true);
        $group->setMuet(true);
        $group->setHeight("150");
        $group->setWidth("300");
        $group->setPositionX(19.8);
        $group->setPositionY(20.1);

        $this->addReference('camera-group-louvard-alpha', $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function setCameraGroupBeta(ObjectManager $manager): void
    {
        $group = new CameraGroup();
        $group->setName("Camera Beta");
        $group->setVisible(true);
        $group->setMuet(true);
        $group->setHeight("150");
        $group->setWidth("300");
        $group->setPositionX(13.8);
        $group->setPositionY(32.1);

        $this->addReference('camera-group-louvard-beta', $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function setInfoGroup(ObjectManager $manager): void
    {
        $group = new InfoGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Info Louvard'));
        $group->setTitre('Louvard 2023');
        $group->setLogo('https://cdn.streamcave.tv/louvard/logo.svg');
        $group->setDescription('Groupe info de la Louvard 2023');
        $group->setTextScroll(['Bienvenue sur la Louvard 2023 !', 'Sixquatre assure le Cast de ce tournoi R6 en compagnie de StreamCave.', 'Un grand merci aux belges de nous accueillir !', 'Bonne chance à tous !']);

        $this->addReference('info-group-louvard', $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function setMatchGroup(ObjectManager $manager): void
    {
        $group = new MatchGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Match Louvard'));
        $group->setTeamNameA('Alpha');
        $group->setLogoTeamA('https://cdn.streamcave.tv/teams/alpha.png');
        $group->setTeamNameB('Beta');
        $group->setLogoTeamB('https://cdn.streamcave.tv/teams/beta.png');
        $group->setStartDate(new \DateTimeImmutable("2023-03-31 12:00:00"));

        $manager->persist($group);
        $manager->flush();
        $this->addReference('match-group-louvard', $group);

    }

    public function setPollGroup(ObjectManager $manager): void
    {
        $group = new PollGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Poll'));
        $group->setQuestion('Quel joueur est en attaque ?');
        $group->setAnswers(['Ace', 'Castle', 'Pulse']);
        $group->setGoodAnswer(['Ace']);
        $group->setTime(300);

        $this->addReference('poll-group-louvard', $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function setPopupGroup(ObjectManager $manager): void
    {
        $group = new PopupGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Popup'));
        $group->setContent('Bienvenue en cettre première édition de la Louvard 2023 !');

        $this->addReference('popup-group-louvard', $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function setTweetGroup(ObjectManager $manager): void
    {
        $group = new TweetGroup();
        $group->setPseudo('BRIETGAME');
        $group->setAt('brietgame');
        $group->setMediaType('image');
        $group->setMediaUrl('https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png');
        $group->setContent('Bienvenue sur la Louvard 2023 !');

        $this->addReference('tweet-group-louvard', $group);

        $manager->persist($group);
        $manager->flush();
    }
}
