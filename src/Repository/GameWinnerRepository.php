<?php

namespace App\Repository;

use App\Entity\GameWinner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameWinner>
 *
 * @method GameWinner|null find($id, $lockMode = null, $lockVersion = null)
 * @method GameWinner|null findOneBy(array $criteria, array $orderBy = null)
 * @method GameWinner[]    findAll()
 * @method GameWinner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameWinnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameWinner::class);
    }

    public function save(GameWinner $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GameWinner $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByGameAndUser(int $gameId, int $userId): ?GameWinner
    {
        try {
            return $this->createQueryBuilder('gw')
                ->andWhere('gw.game = :gameId')
                ->andWhere('gw.user = :userId')
                ->setParameter('gameId', $gameId)
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
