<?php

namespace App\Repository;

use App\Entity\RoomParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoomParticipant>
 *
 * @method RoomParticipant|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoomParticipant|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoomParticipant[]    findAll()
 * @method RoomParticipant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomParticipant::class);
    }

    public function save(RoomParticipant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RoomParticipant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getCountByRoomId(int $room): int
    {
        try {
            $result = $this->createQueryBuilder('rp')
                ->select('COUNT(rp.id)')
                ->andWhere('rp.room = :room')
                ->setParameter('room', $room)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }

        return $result;
    }

    public function findOneByRoomAndUser(int $room, int $user): ?RoomParticipant
    {
        try {
            $result = $this->createQueryBuilder('rp')
                ->andWhere('rp.room = :room_id')
                ->andWhere('rp.user = :user_id')
                ->setParameter('room_id', $room)
                ->setParameter('user_id', $user)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NoResultException | NonUniqueResultException $e) {
            return null;
        }

        return $result;
    }

    public function findByRoom(int $roomId)
    {
        try {
            return $this->createQueryBuilder('rp')
                ->andWhere('rp.room = :room_id')
                ->setParameter('room_id', $roomId)
                ->getQuery()
                ->getResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return null;
        }
    }

    public function findParticipantsByRoomId($roomId)
    {
        try {
            return $this->createQueryBuilder('rp')
                    ->select('rp', 'u.username', 'u.picture')
                    ->leftJoin('rp.user', 'u')
                    ->andWhere('rp.room = :roomId')
                    ->setParameter('roomId', $roomId)
                    ->getQuery()
                    ->getResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return null;
        }
    }

}
