<?php

namespace App\DataFixtures\models\her6s;

use App\DataFixtures\MapsFixtures;
use App\DataFixtures\OverlayFixtures;
use App\Entity\AnswerGroup;
use App\Entity\Camera;
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
    private const MODEL = 'her6s';

    public function __construct(LibMapRepository $libMapRepository)
    {
        $this->libMapRepository = $libMapRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $this->setCameraGroup($manager);
        $this->setInfoGroup($manager);
    }

    private function setCameraGroup(ObjectManager $manager): void
    {
        $players = ['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo'];
        $playersVS = ['Foxtrot', 'Golf', 'Hotel', 'India', 'Juliett'];
        $cameraGroup1 = new CameraGroup();
        $cameraGroup1->setUuid(Uuid::v5(Uuid::v6(), "Camera Groupe"));

        foreach ($players as $key => $player) {
            $camera = new Camera();
            $camera->setUuid(Uuid::v5(Uuid::v6(), "Camera $player"));
            $camera->setVisible(false);
            $camera->setSocketId("socket-$player");
            $camera->addCameraGroup($cameraGroup1);

            $manager->persist($camera);
            $manager->flush();
        }
        $this->addReference("camera-group-1" . self::MODEL, $cameraGroup1);

        $cameraGroup2 = new CameraGroup();
        $cameraGroup2->setUuid(Uuid::v5(Uuid::v6(), "Camera Groupe"));

        foreach ($players as $key => $player) {
            $camera = new Camera();
            $camera->setUuid(Uuid::v5(Uuid::v6(), "Camera $player"));
            $camera->setVisible(false);
            $camera->setSocketId("socket-$player");
            $camera->addCameraGroup($cameraGroup2);

            $manager->persist($camera);
            $manager->flush();
        }
        $this->addReference("camera-group-2" . self::MODEL, $cameraGroup1);
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

    public function getDependencies(): array
    {
        return [
            MapsFixtures::class,
            OverlayFixtures::class
        ];
    }
}
