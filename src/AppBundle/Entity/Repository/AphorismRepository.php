<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/14/15
 * Time: 4:37 PM
 */

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;


/**
 * Class AphorismRepository
 * @package AppBundle\Entity\Repository
 */
class AphorismRepository extends EntityRepository
{
    /**
     * @param $goal
     * @return array|null
     */
    public function findOneRandom($goal, $getSingle = false)
    {
        $tags = [];
        foreach($goal->getTags() as $tag){
            $tags[] = $tag->getId();
        }

        if(count($tags) == 0){
            return [];
        }

        $aphorisms = $this->getEntityManager()
            ->createQuery("SELECT a
                           FROM AppBundle:Aphorism a
                           JOIN a.tags t WITH t.id IN (:tags)")
            ->setParameter('tags', $tags)
            ->getResult();

        shuffle($aphorisms);

        if ($getSingle){
            $aphorisms = [$aphorisms[0]];
        }

        return $aphorisms;
    }
}