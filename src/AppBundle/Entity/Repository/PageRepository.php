<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/16/15
 * Time: 8:48 PM
 */

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * Class PageRepository
 * @package AppBundle\Entity\Repository
 */
class PageRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAllByOrdered()
    {
        $query =
            $this->getEntityManager()
                ->createQueryBuilder()
                ->addSelect('p')
                ->from('AppBundle:Page', 'p')
                ->orderBy('p.position', 'ASC')
        ;

        $query->getQuery()->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');

        return $query->getQuery()
            ->useResultCache(true, 24 * 3600, 'pages')
            ->getResult();
    }
}