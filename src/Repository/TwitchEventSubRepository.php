<?php

namespace App\Repository;

use App\Entity\TwitchEventSub;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TwitchEventSub>
 *
 * @method TwitchEventSub|null find($id, $lockMode = null, $lockVersion = null)
 * @method TwitchEventSub|null findOneBy(array $criteria, array $orderBy = null)
 * @method TwitchEventSub[]    findAll()
 * @method TwitchEventSub[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TwitchEventSubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TwitchEventSub::class);
    }

    public function save(TwitchEventSub $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TwitchEventSub $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TwitchEventSub[] Returns an array of TwitchEventSub objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TwitchEventSub
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
