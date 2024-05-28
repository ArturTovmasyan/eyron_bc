<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/16/15
 * Time: 10:30 AM
 */

namespace AppBundle\Filter;

use AppBundle\Model\PublishAware;
use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Class PublishFilter
 * @package W3\BookBundle\Filter
 */
class PublishFilter extends SQLFilter
{
    /**
     * @param ClassMetaData $targetEntity
     * @param string $targetTableAlias
     * @return string
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        // check publish
        if ($targetEntity->reflClass->implementsInterface('AppBundle\Model\PublishAware')) {

            return $targetTableAlias.'.publish is null or ' . $targetTableAlias . '.publish = '   . PublishAware::PUBLISH ;
        }

        return "";
    }
}