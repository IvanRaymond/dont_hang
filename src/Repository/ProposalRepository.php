<?php

namespace App\Repository;

use App\Entity\Proposal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Proposal>
 *
 * @method Proposal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Proposal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Proposal[]    findAll()
 * @method Proposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProposalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proposal::class);
    }

    public function save(Proposal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Proposal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByGame(int $gameId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.game = :gameId')
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getResult();
    }

    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndGame(int $userId, int $gameId)
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.user = :userId')
                ->andWhere('p.game = :gameId')
                ->setParameter('userId', $userId)
                ->setParameter('gameId', $gameId)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findCorrectByGame(int $gameId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.game = :gameId')
            ->andWhere('p.correct = true')
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getResult();
    }

    public function countProposalsByUserPerDay(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->select('SUBSTRING(p.created_at, 1, 10) AS day', 'COUNT(p.id) AS proposalCount')
            ->andWhere('p.user = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
}
