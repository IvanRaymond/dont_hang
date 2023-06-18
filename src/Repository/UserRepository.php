<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function findOneByUsername(string $username): ?User
    {
        try {
            return $this->createQueryBuilder('u')
                ->andWhere('u.username = :username')
                ->setParameter('username', $username)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function getAllRank()
    {
        $query = $this->getEntityManager()->createQuery('
            SELECT u.id, u.username, u.picture, SUM(gp.points) AS points, COUNT(gp.id) AS gameCount, COUNT(gw.id) AS gamesWon
            FROM App\Entity\User u
            LEFT JOIN App\Entity\GameParticipant gp WITH gp.user = u
            LEFT JOIN App\Entity\GameWinner gw WITH gw.user = u
            GROUP BY u.id
            ORDER BY points DESC
        ');

        return $query->getResult();
    }

    public function getHistory($userId)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
            SELECT g.finished_at AS gameDate, g.duration AS duration, gw.id AS result, g.word AS word, gp.points AS points
            FROM App\Entity\GameParticipant gp
            JOIN gp.game g
            LEFT JOIN App\Entity\GameWinner gw WITH gw.game = g AND gw.user = :userId
            WHERE gp.user = :userId
            AND (gw.id IS NOT NULL OR gw.id IS NULL)
            ORDER BY g.finished_at DESC
        ');

        $query->setParameter('userId', $userId);

        return $query->getResult();
    }
}
