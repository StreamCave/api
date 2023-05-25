<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Model;
use Symfony\Component\Uid\Uuid;

class ModelFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->modelLouvard($manager);
        $this->modelFlowUp($manager);
        $this->modelHER6S($manager);
        $this->modelRoadToLan($manager);
    }

    private function modelLouvard(ObjectManager $manager) {
        $model = new Model();
        $model->setUuid('louvard');
        $model->setName('louvard');
        $model->setDescription('Ceci est le modèle Louvard.');
        $model->setPrice(0);
        $model->setPreview("https://cdn.streamcave.tv/models/louvard/preview.jpg");
        $model->setRules([
            "Maps" => [
                "min" => 1,
                "max" => 9,
                "inTopbar" => true,
                "inBottombar" => true,
            ],
            "Cameras" => [
                "numberOfGroup" => 2,
                "maxPerGroup" => 5,
                "minPerGroup" => 1,
            ]
        ]);
        $model->setTags(["R6", "RocketLeague", "CSGO"]);

        $this->addReference('model-louvard', $model);

        $manager->persist($model);
        $manager->flush();
    }

    private function modelFlowUp(ObjectManager $manager): void
    {
        $model = new Model();
        $model->setUuid('flowup');
        $model->setName('flowup');
        $model->setDescription('Ceci est le modèle FlowUp.');
        $model->setPrice(0);
        $model->setPreview("https://cdn.streamcave.tv/models/flowup/preview.jpg");
        $model->setRules([
            "Maps" => [
                "min" => 1,
                "max" => 3,
                "inTopbar" => true,
                "inBottombar" => true,
            ],
            "Cameras" => [
                "numberOfGroup" => 1,
                "maxPerGroup" => 2,
                "minPerGroup" => 1,
            ],
            "Widgets" => ['topbar', 'bottombar', 'cameras', 'match', 'poll', 'popup', 'tweet', 'maps', 'planning']
        ]);
        $model->setTags(["R6"]);

        $this->addReference('model-flowup', $model);

        $manager->persist($model);
        $manager->flush();
    }

    private function modelHER6S(ObjectManager $manager): void
    {
        $model = new Model();
        $model->setUuid('her6s');
        $model->setName('her6s');
        $model->setDescription('Ceci est le modèle HER6S.');
        $model->setPrice(0);
        $model->setPreview("https://cdn.streamcave.tv/models/her6s/preview.jpg");
        $model->setRules([
            "Maps" => [
                "min" => 1,
                "max" => 3,
                "inTopbar" => true,
                "inBottombar" => true,
            ],
            "Cameras" => [
                "numberOfGroup" => 2,
                "maxPerGroup" => 5,
                "minPerGroup" => 1,
            ],
            "Widgets" => ['topbar', 'bottombar', 'cameras', 'match', 'poll', 'popup', 'tweet', 'maps', 'planning']
        ]);
        $model->setTags(["R6"]);

        $this->addReference('model-her6s', $model);

        $manager->persist($model);
        $manager->flush();
    }

    private function modelRoadToLan(ObjectManager $manager): void
    {
        $model = new Model();
        $model->setUuid('yunktis');
        $model->setName('yunktis');
        $model->setDescription('Ceci est le modèle Yunktis.');
        $model->setPrice(0);
        $model->setPreview("https://cdn.streamcave.tv/models/yunktis/preview.jpg");
        $model->setRules([
            "Maps" => [
                "min" => 1,
                "max" => 3,
                "inTopbar" => true,
                "inBottombar" => true,
            ],
            "Cameras" => [
                "numberOfGroup" => 2,
                "maxPerGroup" => 5,
                "minPerGroup" => 1,
            ],
            "Widgets" => ['topbar', 'cameras']
        ]);
        $model->setTags(["R6"]);

        $this->addReference('model-yunktis', $model);

        $manager->persist($model);
        $manager->flush();
    }
}
