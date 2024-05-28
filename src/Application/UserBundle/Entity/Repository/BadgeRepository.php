<?php

namespace Application\UserBundle\Entity\Repository;

use Application\UserBundle\Entity\Badge;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class BadgeRepository
 * @package Application\UserBundle\Entity\Repository
 */
class BadgeRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function getMinUpdated()
    {
        $stmt = $this->getEntityManager()
            ->getConnection()
            ->prepare('
                        SELECT * FROM
                        (
                          SELECT MAX(badge.updated) as traveller FROM badge WHERE badge.type = :traveller ORDER BY badge.updated DESC  LIMIT 10
                        ) as traveller,
                        
                       (
                          SELECT MAX(badge.updated) as motivator FROM badge WHERE badge.type = :motivator ORDER BY badge.updated DESC LIMIT 10
                        ) as motivator,
                        
                         (
                          SELECT MAX(badge.updated) as innovator FROM badge WHERE badge.type = :innovator ORDER BY badge.updated DESC LIMIT 10
                        ) as innovator

                        ');
        $stmt->bindValue('traveller', Badge::TYPE_TRAVELLER);
        $stmt->bindValue('motivator', Badge::TYPE_MOTIVATOR);
        $stmt->bindValue('innovator', Badge::TYPE_INNOVATOR);
        $stmt->execute();
        $query = $stmt->fetchAll();

        $minUpdate = null;

        if($query){
            $query = reset($query);

            $minUpdate =  $query['traveller'] > $minUpdate ? $query['traveller'] :  $minUpdate;
            $minUpdate =  $query['motivator'] > $minUpdate ? $query['motivator'] :  $minUpdate;
            $minUpdate =  $query['innovator'] > $minUpdate ? $query['innovator'] :  $minUpdate;
        }

        return  $minUpdate;
    }

    /**
     * @param $type
     * @param $count
     * @return array
     */
    public function findTopUsersIdByType($type, $count)
    {
        $topBadges = $this->getEntityManager()
            ->createQuery("SELECT b , u
                           FROM ApplicationUserBundle:Badge b
                           JOIN b.user u
                           WHERE b.type = :types
                           ORDER BY b.score DESC")
            ->setParameter('types', $type)
            ->setMaxResults($count)
            ->getResult();


        return $topBadges;
    }

    /**
     * This function is used to get TOP users by score rating
     * 
     * @param $type
     * @param $count
     * @param $userId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTopUsersByType($type, $count, $userId = null)
    {
        $topBadges = $this->getEntityManager()
            ->createQuery("SELECT b, u
                           FROM ApplicationUserBundle:Badge b
                           JOIN b.user u
                           WHERE b.type = :types
                           ORDER BY b.score DESC, u.id ASC")
            ->setParameter('types', $type)
            ->setMaxResults($count)
            ->getResult();

        foreach($topBadges as $key => $topBadge){
            $topBadge->position = $key + 1;
        }

        if (is_null($userId)){
            return $topBadges;
        }

        $userPosition =  $this->getEntityManager()
            ->createQuery("SELECT COUNT(b.id) as cnt
                           FROM ApplicationUserBundle:Badge b
                           JOIN b.user u
                           WHERE b.type = :types 
                           AND (b.score > (SELECT MAX(b1.score) FROM ApplicationUserBundle:Badge b1 WHERE b1.user = :user AND b1.type = :types)                           
                           OR (b.score = (SELECT MAX(b2.score) FROM ApplicationUserBundle:Badge b2 WHERE b2.user = :user AND b2.type = :types) AND u.id < :user))")
            ->setParameter('types', $type)
            ->setParameter('user', $userId)
            ->setMaxResults($count)
            ->getSingleScalarResult();


        if ($userPosition < 10){
            return $topBadges;
        }

        $nearBadges = $this->getEntityManager()
            ->createQuery("SELECT b, u
                           FROM ApplicationUserBundle:Badge b
                           JOIN b.user u
                           WHERE b.type = :types
                           ORDER BY b.score DESC, u.id ASC")
            ->setParameter('types', $type)
            ->setFirstResult($userPosition - 2 <= 10 ? 10 : $userPosition - 2)
            ->setMaxResults($userPosition - 2 <= 10 ? $userPosition - 7 : 5)
            ->getResult();


        $startPosition = $userPosition - 2 <= 10 ? 10 : $userPosition - 2;
        foreach($nearBadges as $nearBadge){
            $nearBadge->position = $startPosition + 1;
            $startPosition++;
        }

        return array_merge($topBadges, $nearBadges);
    }

    /**
     * @param $userId
     * @param $type
     * @return mixed
     */
    public function findBadgeByUserAndType($userId, $type)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT b
                           FROM ApplicationUserBundle:Badge b
                           JOIN b.user u
                           WHERE b.user = :user AND b.type = :type")
            ->setParameter('type', $type)
            ->setParameter('user', $userId)
            ->getOneOrNullResult();
    }

    /**
     * @return mixed
     */
    public function getMaxScores()
    {
        // default return value
        $result = array(
            Badge::TYPE_TRAVELLER =>1,
            Badge::TYPE_INNOVATOR =>2,
            Badge::TYPE_MOTIVATOR =>1,
            );

        $stmt = $this->getEntityManager()
            ->getConnection()
            ->prepare('
                        SELECT * FROM
                        (
                          SELECT MAX(badge.score) as traveller FROM badge WHERE badge.type = :traveller
                        ) as traveller,
                        
                       (
                          SELECT MAX(badge.score) as motivator FROM badge WHERE badge.type = :motivator
                        ) as motivator,
                        
                         (
                          SELECT MAX(badge.score) as innovator FROM badge WHERE badge.type = :innovator
                        ) as innovator

                        ');
        $stmt->bindValue('traveller', Badge::TYPE_TRAVELLER);
        $stmt->bindValue('motivator', Badge::TYPE_MOTIVATOR);
        $stmt->bindValue('innovator', Badge::TYPE_INNOVATOR);
        $stmt->execute();
        $query = $stmt->fetchAll();

        if($query){
            $query = reset($query);
            $result [Badge::TYPE_TRAVELLER] = $query['traveller'];
            $result [Badge::TYPE_MOTIVATOR] = $query['motivator'];
            $result [Badge::TYPE_INNOVATOR] = $query['innovator'];
        }

        return  $result;
    }

    /**
     * This repository find unique users by id limit
     *
     * @param $begin
     * @param $limit
     * @return array
     */
    public function findByLimit($begin, $limit)
    {
        $query = $this->getEntityManager()
            ->createQuery("SELECT b
                           FROM ApplicationUserBundle:Badge b
                           LEFT JOIN b.user u
                           ORDER BY b.id ASC ")
            ->setFirstResult($begin)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        return $paginator->getIterator()->getArrayCopy();
    }
}
