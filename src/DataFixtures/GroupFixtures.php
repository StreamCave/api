<?php

namespace App\DataFixtures;

use App\Entity\CameraGroup;
use App\Entity\InfoGroup;
use App\Entity\MapGroup;
use App\Entity\MatchGroup;
use App\Entity\PlanningGroup;
use App\Entity\PollGroup;
use App\Entity\PopupGroup;
use App\Entity\TweetGroup;
use App\Repository\LibMapRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(LibMapRepository $libMapRepository)
    {
        $this->libMapRepository = $libMapRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $this->setCameraGroup($manager);
        $this->setInfoGroup($manager);
        $this->setMatchGroup($manager);
        $this->setPollGroup($manager);
        $this->setPopupGroup($manager);
        $this->setTweetGroup($manager);
        $this->setMapGroupBO3($manager);
        $this->setPlanningGroup($manager);
    }

    private function setCameraGroup(ObjectManager $manager): void
    {
        $players = ['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo', 'Foxtrot', 'Golf', 'Hotel', 'India', 'Juliett'];
        $positionX = [19.8, 22.0, 24.2, 26.4, 28.6, 30.8, 33.0, 35.2, 37.4, 39.6];
        $positionY = [20.4, 21.4, 22.4, 23.4, 24.4, 25.4, 26.4, 27.4, 28.4, 29.4];


        foreach ($players as $key => $player) {
            $camera = new CameraGroup();
            $camera->setUuid(Uuid::v5(Uuid::v6(), "Camera $player"));
            $camera->setName("Camera $player");
            $camera->setVisible(false);
            $camera->setMuet(true);
            $camera->setHeight("150");
            $camera->setWidth("300");
            $camera->setPositionX($positionX[$key]);
            $camera->setPositionY($positionY[$key]);

            $this->addReference("camera-group-louvard-$player", $camera);

            $manager->persist($camera);
            $manager->flush();
        }
    }

    private function setInfoGroup(ObjectManager $manager): void
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

    private function setMatchGroup(ObjectManager $manager): void
    {
        $group = new MatchGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Match Louvard'));
        $group->setTeamNameA('Alpha');
        $group->setLogoTeamA('https://cdn.streamcave.tv/teams/alpha.png');
        $group->setPlayersTeamA(['Ace', 'Castle', 'Pulse', 'Thatcher', 'Thermite']);
        $group->setScoreA("3");
        $group->setTeamNameB('Beta');
        $group->setLogoTeamB('https://cdn.streamcave.tv/teams/beta.png');
        $group->setPlayersTeamB(['Ash', 'Blackbeard', 'Capitao', 'Doc', 'Montagne']);
        $group->setScoreB("1");
        $group->setStartDate(new \DateTimeImmutable("2023-03-31 12:00:00"));

        $manager->persist($group);
        $manager->flush();
        $this->addReference('match-group-louvard', $group);

    }

    private function setPollGroup(ObjectManager $manager): void
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

    private function setPopupGroup(ObjectManager $manager): void
    {
        $group = new PopupGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Popup'));
        $group->setContent('Bienvenue en cettre première édition de la Louvard 2023 !');

        $this->addReference('popup-group-louvard', $group);

        $manager->persist($group);
        $manager->flush();
    }

    private function setTweetGroup(ObjectManager $manager): void
    {
        $group = new TweetGroup();
        $group->setPseudo('BRIETGAME');
        $group->setAt('brietgame');
        $group->setMediaType('image');
        $group->setMediaUrl('https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png');
        $group->setContent('Bienvenue sur la Louvard 2023 !');
        $group->setVisible(false);
        $group->setOverlayId($this->getReference('overlay-louvard')->getUuid());
        $group->setHashtag('Louvard2023');

        $this->addReference('tweet-group-louvard', $group);

        $manager->persist($group);
        $manager->flush();
    }

    private function setMapGroupBO3(ObjectManager $manager): void
    {
        $maps = ['border', 'oregon', 'kafe'];
        $isPick = [true, true, false];
        $winTeam = ['Alpha', 'Alpha', 'Beta'];

        foreach ($maps as $key => $map) {
            $group = new MapGroup();
            $group->setUuid(Uuid::v5(Uuid::v6(), $map));
            $group->setLibMap($this->getReference('map-' . $map));
            $group->setPick($isPick[$key]);
            $group->setWinTeam($winTeam[$key]);
            $this->addReference('map-group-louvard-bo3-' . $map, $group);

            $manager->persist($group);
            $manager->flush();
        }
    }

    private function setPlanningGroup(ObjectManager $manager): void
    {
        $dates = [new \DateTimeImmutable("2023-03-31 12:00:00"), new \DateTimeImmutable("2023-03-31 14:00:00"), new \DateTimeImmutable("2023-03-31 16:00:00")];
        $teamA = ['Alpha', 'Beta', 'Charlie'];
        $logoA = ['https://cdn.streamcave.tv/teams/alpha.png', 'https://cdn.streamcave.tv/teams/beta.png', 'https://cdn.streamcave.tv/teams/alpha.png'];
        $teamB = ['Delta', 'Echo', 'Foxtrot'];
        $logoB = ['https://cdn.streamcave.tv/teams/beta.png', 'https://cdn.streamcave.tv/teams/alpha.png', 'https://cdn.streamcave.tv/teams/beta.png'];

        foreach ($dates as $key => $date) {
            $group = new PlanningGroup();
            $group->setUuid(Uuid::v5(Uuid::v6(), 'Planning Louvard ' . $teamA[$key] . ' vs ' . $teamB[$key]));
            $group->setTeamA($teamA[$key]);
            $group->setLogoA($logoA[$key]);
            $group->setTeamB($teamB[$key]);
            $group->setLogoB($logoB[$key]);
            $group->setStartDate($date);
            $this->addReference('planning-group-louvard-' . $teamA[$key] . '-vs-' . $teamB[$key], $group);

            $manager->persist($group);
            $manager->flush();
        }
    }

    public function getDependencies(): array
    {
        return [
            MapsFixtures::class,
            OverlayFixtures::class
        ];
    }
}
