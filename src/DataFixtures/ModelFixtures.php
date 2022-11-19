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
        $model = new Model();
        $model->setUuid(Uuid::v5(Uuid::v6(), 'Default Model'));
        $model->setName('Default Model');
        $model->setDescription('Ceci est le modèle par défaut.');
        $model->setPrice(0);

        $this->addReference('default-model', $model);

        $manager->persist($model);
        $manager->flush();
    }
}
