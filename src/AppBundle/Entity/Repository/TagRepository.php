<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/11/15
 * Time: 10:23 AM
 */

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;


class TagRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getTagTitles()
    {
        $result = array();

        $query =  $this->getEntityManager()
            ->createQuery("SELECT t.tag
                           FROM AppBundle:Tag t ")
            ->getResult(Query::HYDRATE_ARRAY)
            ;

        if($query){
            $result =  array_map(function($value) { return $value['tag']; }, $query);
        }
        return $result;
    }


    /**
     * @param $titles
     * @return array
     */
    public function findTagsByTitles($titles)
    {
        $result = array();

        if(count($titles) > 0){
            $result =  $this->getEntityManager()
                ->createQuery("SELECT t
                           FROM AppBundle:Tag t WHERE t.tag in (:titles)")
                ->setParameter('titles', $titles)
                ->getResult()
            ;
        }


        return $result;
    }

    /**
     * This function is used to get tags by goal and tag id
     *
     * @param $tagsIds
     * @param $goalId
     * @return array
     */
    public function findTagsByIds($tagsIds, $goalId)
    {
        $result = $this->getEntityManager()
            ->createQuery("SELECT t
                       FROM AppBundle:Tag t
                       LEFT JOIN t.goal g
                       WHERE t.id in (:tagIds) and g.id = :gId")
            ->setParameter('tagIds', $tagsIds)
            ->setParameter('gId', $goalId)
            ->getResult();

        return $result;
    }

}
