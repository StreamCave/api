<?php

namespace App\Repository;

use App\Entity\R6StatsPlayers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<R6StatsPlayers>
 *
 * @method R6StatsPlayers|null find($id, $lockMode = null, $lockVersion = null)
 * @method R6StatsPlayers|null findOneBy(array $criteria, array $orderBy = null)
 * @method R6StatsPlayers[]    findAll()
 * @method R6StatsPlayers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class R6StatsPlayersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, R6StatsPlayers::class);
    }

    public function save(R6StatsPlayers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(R6StatsPlayers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return R6StatsPlayers[] Returns an array of R6StatsPlayers objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?R6StatsPlayers
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
