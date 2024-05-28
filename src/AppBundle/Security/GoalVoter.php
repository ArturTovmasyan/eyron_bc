<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 6/28/16
 * Time: 4:56 PM
 */
namespace AppBundle\Security;

use AppBundle\Entity\Goal;
use AppBundle\Model\PublishAware;
use Application\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GoalVoter extends Voter
{
    const VIEW   = 'view';
    const EDIT   = 'edit';
    const DELETE = 'delete';
    const ADD    = 'add';
    const DONE   = 'done';

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE, self::ADD, self::DONE))) {
            return false;
        }

        if (!$subject instanceof Goal) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $goal
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $goal, TokenInterface $token)
    {
        $user = $token->getUser();

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($goal, $user);
            case self::EDIT:
                return $this->canEdit($goal, $user);
            case self::DELETE:
                return $this->canDelete($goal, $user);
            case self::ADD:
                return $this->canAdd($goal, $user);
            case self::DONE:
                return $this->canComplete($goal, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param Goal $goal
     * @param $user
     * @return bool
     */
    private function canView(Goal $goal, $user)
    {
        if ($user instanceof User && ($user->isAdmin() || $user->hasRole('ROLE_MODERATOR'))){
            return true;
        }

        return ($goal->getPublish() == PublishAware::PUBLISH || ($user instanceof User && !is_null($goal->getAuthor()) && $goal->getAuthor()->getId() == $user->getId()));
    }

    /**
     * @param Goal $goal
     * @param $user
     * @return bool
     */
    private function canEdit(Goal $goal, $user)
    {
        if (!$user instanceof User){
            return false;
        }

        if ($user->isAdmin()){
            return true;
        }

        if ($goal->getPublish() == PublishAware::NOT_PUBLISH &&
            !is_null($author = $goal->getAuthor()) && $user->getId() == $author->getId()){
            return true;
        }

        return false;
    }

    /**
     * @param Goal $goal
     * @param $user
     * @return bool
     */
    public function canDelete(Goal $goal, $user)
    {
        return $this->canEdit($goal, $user);
    }

    /**
     * @param Goal $goal
     * @param $user
     * @return bool
     */
    public function canAdd(Goal $goal, $user)
    {
        if (!$user instanceof User){
            return false;
        }

        return $goal->getPublish() || (!is_null($author = $goal->getAuthor()) && $user->getId() == $author->getId());
    }

    /**
     * @param Goal $goal
     * @param $user
     * @return bool
     */
    public function canComplete(Goal $goal, $user)
    {
        return $this->canAdd($goal, $user);
    }
}