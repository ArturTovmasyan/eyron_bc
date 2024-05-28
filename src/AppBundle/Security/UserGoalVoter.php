<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 6/28/16
 * Time: 4:56 PM
 */
namespace AppBundle\Security;

use AppBundle\Entity\Goal;
use AppBundle\Entity\UserGoal;
use AppBundle\Model\PublishAware;
use Application\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserGoalVoter extends Voter
{
    const VIEW   = 'view';
    const EDIT   = 'edit';
    const DELETE = 'delete';

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        if (!$subject instanceof UserGoal) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $userGoal
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $userGoal, TokenInterface $token)
    {
        $user = $token->getUser();

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($userGoal, $user);
            case self::EDIT:
                return $this->canEdit($userGoal, $user);
            case self::DELETE:
                return $this->canDelete($userGoal, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param UserGoal $userGoal
     * @param $user
     * @return bool
     */
    private function canView(UserGoal $userGoal, $user)
    {
        if (!$user instanceof User){
            return false;
        }

        if ($userGoal->getIsVisible() || $userGoal->getUser()->getId() == $user->getId()){
            return true;
        }

        return false;
    }

    /**
     * @param UserGoal $userGoal
     * @param $user
     * @return bool
     */
    private function canEdit(UserGoal $userGoal, $user)
    {
        if (!$user instanceof User){
            return false;
        }

        if ($user->isAdmin()){
            return true;
        }

        if ($user->getId() == $userGoal->getUser()->getId()){
            return true;
        }

        return false;
    }

    /**
     * @param UserGoal $userGoal
     * @param $user
     * @return bool
     */
    public function canDelete(UserGoal $userGoal, $user)
    {
        return $this->canEdit($userGoal, $user);
    }
}