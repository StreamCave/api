<?php

namespace App\DataFixtures;

use App\Entity\LibMap;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MapsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->setupMapR6($manager);

        $manager->flush();
    }

    private function setupMapR6(ObjectManager $manager): void
    {
        $mapsName = ["Bartlett", "Border", "Club_house", "Close_Quarters", "Nighthaven_Labs", "Stadium", "Yatch", "Villa", "Tower", "Theme Park", "Skyscraper", "Plane", "Outback", "Oregon", "Kanal", "Kafe", "House", "Hereford", "Fortress", "Favela", "Consulate", "Coastline", "Chalet", "Bank", "Emerald_Plains"];
        $mapsUri = ["bartlett", "border", "clubhouse", "closequarters", "nighthaven_labs", "stadium", "yatch", "villa", "tower", "themepark", "skyscraper", "plane", "outback", "oregon", "kanal", "kafe", "house", "hereford", "fortress", "favela", "consulate", "coastline", "chalet", "bank", "emerald"];

        foreach ($mapsName as $key => $mapName) {
            $map = new LibMap();
            $map->setName($mapName);
            $map->setImage("https://cdn.streamcave.tv/r6/maps/{$mapsUri[$key]}.webp");
            $this->addReference("map-{$mapsUri[$key]}", $map);
            $manager->persist($map);
        }
    }
}
