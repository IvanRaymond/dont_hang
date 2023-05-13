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
    private ManagerRegistry $managerRegistry;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomParticipant::class);
        $this->managerRegistry = $registry;
    }

    public function save(RoomParticipant $entity, bool $flush = false): void
    {
        // Check if room is full
        $room_repo = new RoomRepository($this->managerRegistry);
        try {
            $room = $room_repo->findOneById($entity->getRoom()->getId());
            if ($room->getCapacity() <= $this->getCountByRoomId($entity->getRoom()->getId())) {
                $this->getEntityManager()->persist($entity);

                if ($flush) {
                    $this->getEntityManager()->flush();
                }
            }
        } catch (\Exception $e) {
            return;
        }
    }

    public function remove(RoomParticipant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getCountByRoomId(int $room_id): int
    {
        return $this->createQueryBuilder('rp')
            ->select('COUNT(rp.id)')
            ->andWhere('rp.room_id = :room_id')
            ->setParameter('room_id', $room_id)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
