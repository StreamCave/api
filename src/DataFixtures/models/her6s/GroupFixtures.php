<?php

namespace App\DataFixtures\models\her6s;

use App\DataFixtures\OverlayFixtures;
use App\Entity\CameraGroup;
use App\Entity\InfoGroup;
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

        foreach ($players as $key => $player) {
            $camera = new CameraGroup();
            $camera->setUuid(Uuid::v5(Uuid::v6(), "Camera $player"));
            $camera->setName("Camera $player");
            $camera->setVisible(false);
            $camera->setSocketId("socket-$player");
            $camera->setTeam("Alpha");
            $camera->setMetadata("");
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

    public function getDependencies(): array
    {
        return [
            OverlayFixtures::class
        ];
    }
}
