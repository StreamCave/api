<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUuid(Uuid::v6());
        $user->setEmail($_ENV['ADMIN_EMAIL']);
        $user->setPassword(password_hash($_ENV['ADMIN_PASSWORD'], PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $this->addReference('default-admin-user', $user);

        $manager->persist($user);
        $manager->flush();
    }
}
