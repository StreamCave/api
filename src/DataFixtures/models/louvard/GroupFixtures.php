<?php

namespace App\DataFixtures\models\louvard;

use App\DataFixtures\MapsFixtures;
use App\DataFixtures\OverlayFixtures;
use App\Entity\AnswerGroup;
use App\Entity\Brackets;
use App\Entity\CameraGroup;
use App\Entity\InfoGroup;
use App\Entity\MapGroup;
use App\Entity\MatchGroup;
use App\Entity\PlanningGroup;
use App\Entity\PollGroup;
use App\Entity\PopupGroup;
use App\Entity\TweetGroup;
use App\Entity\TwitchGroup;
use App\Repository\LibMapRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{
    private const MODEL = 'louvard-1';

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
        $this->setTwitchPoll($manager);
        $this->setTwitchPrediction($manager);
    }

    private function setCameraGroup(ObjectManager $manager): void
    {
        $players = ['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo', 'Foxtrot', 'Golf', 'Hotel', 'India', 'Juliett'];

        foreach ($players as $key => $player) {
            $camera = new CameraGroup();
            $camera->setUuid(Uuid::v5(Uuid::v6(), "Camera $player"));
            $camera->setName("Camera $player");
            $camera->setVisible(false);
            $camera->setSocketId("socket-$player");
            $camera->setTeam($key < 5 ? 'Alpha' : 'Beta');
            $this->addReference("camera-group-" . self::MODEL . "-$player", $camera);

            $manager->persist($camera);
            $manager->flush();
        }
    }

    private function setInfoGroup(ObjectManager $manager): void
    {
        $group = new InfoGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Info FlowUp'));
        $group->setTitre('#SaltyDuels');
        $group->setLogo('https://cdn.streamcave.tv/models/flowup/FlowUpSigneWhite.svg');
        $group->setDescription('Groupe info des Salty Duels');
        $group->setTextScroll(['Bienvenue sur les SaltyDuels by FlowUp !', 'Sixquatre assure le Cast de ce tournoi R6 en compagnie de StreamCave et de FlowUp.', 'Bonne chance à tous !']);

        $this->addReference('info-group-' . self::MODEL, $group);

        $manager->persist($group);
        $manager->flush();
    }

    private function setMatchGroup(ObjectManager $manager): void
    {
        $group = new MatchGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Match FlowUp'));
        $group->setTeamNameA('Alpha');
        $group->setLogoTeamA('https://cdn.streamcave.tv/teams/alpha.png');
        $group->setPlayersTeamA(['Ace', 'Castle', 'Pulse', 'Thatcher', 'Thermite']);
        $group->setScoreA("3");
        $group->setTeamNameB('Beta');
        $group->setLogoTeamB('https://cdn.streamcave.tv/teams/beta.png');
        $group->setPlayersTeamB(['Ash', 'Blackbeard', 'Capitao', 'Doc', 'Montagne']);
        $group->setScoreB("1");
        $group->setRounds("BO1");
        $group->setStartDate(new \DateTimeImmutable("2023-03-31 12:00:00"));
        $group->setNextMatch(false);
        $group->setOverlayId($this->getReference('overlay-' . self::MODEL)->getUuid());

        $manager->persist($group);
        $manager->flush();
        $this->addReference('match-group-' . self::MODEL, $group);

    }

    private function setPollGroup(ObjectManager $manager): void
    {
        $group = new PollGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Poll'));
        $group->setQuestion('Quel joueur est en attaque ?');
        $group->setTime(300);
        $group->setChannel("sixquatre");
        $group->setOverlayId($this->getReference('overlay-' . self::MODEL)->getUuid());
        $group->setPollStarted(false);
        $group->setVisible(false);
        $group->setChoices(['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo', 'Foxtrot', 'Golf', 'Hotel', 'India', 'Juliett']);

        $this->addReference('poll-group-' . self::MODEL, $group);

        $manager->persist($group);
        $manager->flush();
    }

    private function setAnswers(ObjectManager $manager): void
    {
        $group = $this->getReference('poll-group-' . self::MODEL);
        $answers = ['Yes', 'No'];

        foreach ($answers as $key => $answer) {
            $answer = new AnswerGroup();
            $answer->setUuid(Uuid::v5(Uuid::v6(), $answer));
            $answer->setAnswer($answer);
            $answer->setPollGroup($group);
            $answer->setVote("Yes");
            $answer->setUsernameVoter("BRIETGAME");

            $manager->persist($answer);
            $manager->flush();
        }
    }

    private function setPopupGroup(ObjectManager $manager): void
    {
        $group = new PopupGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Default Popup'));
        $group->setContent('Bienvenue en cettre première édition de 2023 !');

        $this->addReference('popup-group-' . self::MODEL, $group);

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
        $group->setContent('Bienvenue sur les SaltyDuels by FlowUp !');
        $group->setVisible(false);
        $group->setOverlayId($this->getReference('overlay-' . self::MODEL)->getUuid());
        $group->setHashtag('saltyduels');

        $this->addReference('tweet-group-' . self::MODEL, $group);

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
            $group->setPickTeam("Alpha");
            $group->setWinTeam($winTeam[$key]);
            $group->setStatus("BAN");
            $this->addReference('map-group-' . self::MODEL . '-bo3-' . $map, $group);

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
            $group->setUuid(Uuid::v5(Uuid::v6(), 'Planning Flowup ' . $teamA[$key] . ' vs ' . $teamB[$key]));
            $group->setTeamA($teamA[$key]);
            $group->setLogoA($logoA[$key]);
            $group->setTeamB($teamB[$key]);
            $group->setLogoB($logoB[$key]);
            $group->setStartDate($date);
            $this->addReference('planning-group-' . self::MODEL . '-' . $teamA[$key] . '-vs-' . $teamB[$key], $group);

            $manager->persist($group);
            $manager->flush();
        }
    }

    private function setTwitchPoll(ObjectManager $manager): void
    {
        $group = new TwitchGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Twitch Poll'));
        $group->setData([
            'id' => '123456789',
            'broadcaster_id' => '123456789',
            'broadcaster_name' => 'brietgame',
            'title' => 'Quel joueur est en attaque ?',
            'choices' => [
                [
                    'id' => '123456789',
                    'title' => 'Alpha',
                    'votes' => 0,
                    'channel_points_votes' => 0,
                    'bits_votes' => 0
                ],
                [
                    'id' => '123456789',
                    'title' => 'Bravo',
                    'votes' => 0,
                    'channel_points_votes' => 0,
                    'bits_votes' => 0
                ],
                [
                    'id' => '123456789',
                    'title' => 'Charlie',
                    'votes' => 0,
                    'channel_points_votes' => 0,
                    'bits_votes' => 0
                ],
                [
                    'id' => '123456789',
                    'title' => 'Delta',
                    'votes' => 0,
                    'channel_points_votes' => 0,
                    'bits_votes' => 0
                ],
                [
                    'id' => '123456789',
                    'title' => 'Echo',
                    'votes' => 0,
                    'channel_points_votes' => 0,
                    'bits_votes' => 0
                ],
                [
                    'id' => '123456789',
                    'title' => 'Foxtrot',
                    'votes' => 0,
                    'channel_points_votes' => 0,
                    'bits_votes' => 0
                ],
                [
                    'id' => '123456789',
                    'title' => 'Golf',
                    'votes' => 0,
                    'channel_points_votes' => 0,
                    'bits_votes' => 0
                ],
                [
                    'id' => '123456789',
                    'title' => 'Hotel',
                    'votes' => 0,
                    'channel_points_votes' => 0,
                    'bits_votes' => 0
                ],
                [
                    'id' => '123456789',
                    'title' => 'India',
                    'votes' => 0,
                    'channel_points_votes' => 0,
                    'bits_votes' => 0
                ]
            ]
        ]);

        $this->addReference('twitch-poll-' . self::MODEL, $group);

        $manager->persist($group);
        $manager->flush();
    }

    private function setTwitchPrediction(ObjectManager $manager): void
    {
        $group = new TwitchGroup();
        $group->setUuid(Uuid::v5(Uuid::v6(), 'Twitch Prediction'));
        $group->setData([
            'id' => '123456789',
            'broadcaster_id' => '123456789',
            'broadcaster_name' => 'brietgame',
            'title' => 'Qui va gagner ?',
            'outcomes' => [
                [
                    'id' => '123456789',
                    'title' => 'Alpha',
                    'color' => 'blue',
                    'users' => 0,
                    'channel_points' => 0,
                    'bits' => 0
                ],
                [
                    'id' => '123456789',
                    'title' => 'Bravo',
                    'color' => 'red',
                    'users' => 0,
                    'channel_points' => 0,
                    'bits' => 0
                ]
            ]
        ]);

        $this->addReference('twitch-prediction-' . self::MODEL, $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            MapsFixtures::class,
            OverlayFixtures::class
        ];
    }
}
