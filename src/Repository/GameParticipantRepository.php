<?php

namespace App\Repository;

use App\Entity\GameParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameParticipant>
 *
 * @method GameParticipant|null find($id, $lockMode = null, $lockVersion = null)
 * @method GameParticipant|null findOneBy(array $criteria, array $orderBy = null)
 * @method GameParticipant[]    findAll()
 * @method GameParticipant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameParticipant::class);
    }

    public function save(GameParticipant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GameParticipant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByGameAndUser(int $gameId, int $userId)
    {
        try {
            return $this->createQueryBuilder('gp')
                ->andWhere('gp.game = :gameId')
                ->andWhere('gp.user = :userId')
                ->setParameter('gameId', $gameId)
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
