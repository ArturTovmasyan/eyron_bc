<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/16/15
 * Time: 10:30 AM
 */

namespace AppBundle\Filter;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Class VisibilityFilter
 * @package AppBundle\Filter
 */

class VisibilityFilter extends SQLFilter
{
    /**
     * @param ClassMetaData $targetEntity
     * @param string $targetTableAlias
     * @return string
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $userId  = -1;
        if ($this->hasParameter('userId')){
            $userId = $this->getParameter('userId');
        }

        if ($targetEntity->name == 'AppBundle\Entity\SuccessStory') {
            return "COALESCE((SELECT ug.is_visible FROM users_goals as ug WHERE ug.user_id = $targetTableAlias.user_id AND ug.goal_id = $targetTableAlias.goal_id), true) = true OR $targetTableAlias.user_id = $userId";
        }
        elseif ($targetEntity->name == 'Application\CommentBundle\Entity\Comment') {
            return "COALESCE((SELECT ug.is_visible FROM users_goals as ug WHERE ug.user_id = $targetTableAlias.author_id AND ug.goal_id = $targetTableAlias.thread_id), true) = true OR $targetTableAlias.author_id = $userId";
        }
        elseif ($targetEntity->name == 'AppBundle\Entity\UserGoal') {
            return "$targetTableAlias.is_visible = true OR $targetTableAlias.user_id = $userId";
        }

        return "";
    }
}