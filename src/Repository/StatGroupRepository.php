<?php

namespace App\Repository;

use App\Entity\StatGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StatGroup>
 *
 * @method StatGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatGroup[]    findAll()
 * @method StatGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatGroup::class);
    }

    public function save(StatGroup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StatGroup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // public function findAllMatchs($uuid): array
    // {
    //     return $this->createQueryBuilder('s')
    //         ->select('s.match_id', 's.overlay_id', 's.status', 's.score', 'rsp.id AS player_id', 'rsp.round', 'rsp.pseudo', 'rsp.kills', 'rsp.deaths', 'rsp.hp', 'rsp.score AS player_score', 'rsp.operator', 'rsp.team')
    //         ->Join('r6_stats_players', 'rsp', 'ON', 's.match_id = rsp.match_id')
    //         ->where('s.overlay_id = :overlayId')
    //         ->setParameter('overlayId', $uuid)
    //         ->getQuery()
    //         ->getArrayResult()
    //     ;
    // }
//    /**
//     * @return StatGroup[] Returns an array of StatGroup objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StatGroup
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
