<?php

namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 *
 * @method Room|null find($id, $lockMode = null, $lockVersion = null)
 * @method Room|null findOneBy(array $criteria, array $orderBy = null)
 * @method Room[]    findAll()
 * @method Room[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    public function save(Room $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Room $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneById(int $id): ?Room
    {
        try {
            return $this->createQueryBuilder('r')
                ->andWhere('r.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * return rooms which is finished
     */
    public function findRoomsByCapacityAndUser($user_id)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->innerJoin('r.roomParticipants', 'rp')
            ->leftJoin('r.games', 'g')
            ->leftJoin('g.gameParticipants', 'gp')
            ->leftJoin('g.gameWinners', 'gw', 'WITH', 'gw.game = g AND gw.user = gp.user')
            ->where($qb->expr()->eq('r.game_count', 1))
            ->andWhere($qb->expr()->eq('rp.user', ':user_id'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->gte('gp.attempts', 7),
                    $qb->expr()->isNotNull('gw.id')
                )
            )
            ->setParameter('user_id', $user_id);

        return $qb->getQuery()->getResult();
    }
}
