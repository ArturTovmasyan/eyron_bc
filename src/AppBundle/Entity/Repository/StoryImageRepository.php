<?php

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class StoryImageRepository
 * @package AppBundle\Entity\Repository
 */
class StoryImageRepository extends EntityRepository
{
    /**
     * @param $ids
     * @return array
     */
    public function findByIDs($ids)
    {
        $query =  $this->getEntityManager()
            ->createQuery(" SELECT i
                            FROM AppBundle:StoryImage i
                            WHERE i.id in (:ids)
                            ")
            ->setParameter('ids', $ids)
            ->getResult()
        ;

        return $query;
    }
}
