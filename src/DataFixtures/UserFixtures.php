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
        $this->setSuperAdmin($manager);
        $this->setSuperAdmin2($manager);
        $this->setSuperAdmin3($manager);
        $this->setUsers($manager);
    }

    private function  setSuperAdmin(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUuid(Uuid::v4());
        $user->setEmail($_ENV['ADMIN_EMAIL']);
        $user->setPseudo("ADMIN Alexis");
        $user->setPassword(password_hash($_ENV['ADMIN_PASSWORD'], PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $this->addReference('default-admin-user', $user);

        $manager->persist($user);
        $manager->flush();
    }

    private function  setSuperAdmin2(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUuid(Uuid::v1());
        $user->setEmail($_ENV['ADMIN_2_EMAIL']);
        $user->setPseudo("ADMIN Jémérmy");
        $user->setPassword(password_hash($_ENV['ADMIN_2_PASSWORD'], PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $this->addReference('default-admin-user-2', $user);

        $manager->persist($user);
        $manager->flush();
    }

    private function setSuperAdmin3(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUuid(Uuid::v1());
        $user->setEmail($_ENV['ADMIN_3_EMAIL']);
        $user->setPseudo("ADMIN Lilian");
        $user->setPassword(password_hash($_ENV['ADMIN_3_PASSWORD'], PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $this->addReference('default-admin-user-3', $user);

        $manager->persist($user);
        $manager->flush();
    }

    private function  setUsers(ObjectManager $manager): void
    {
        $users = ["Alpha","Beta","Charlie","Delta"];

        foreach ($users as $key => $item) {
            $user = new User();
            $user->setUuid(Uuid::v6());
            $user->setEmail($item . '@streamcave.tv');
            $user->setPseudo("User " . $item);
            $user->setPassword(password_hash($item, PASSWORD_BCRYPT));
            $user->setRoles(['ROLE_USER']);

            $this->addReference('default-user-' . $item, $user);

            $manager->persist($user);
            $manager->flush();
        }
    }
}
