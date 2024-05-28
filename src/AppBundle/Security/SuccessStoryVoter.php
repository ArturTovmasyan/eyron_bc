<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 6/28/16
 * Time: 4:56 PM
 */
namespace AppBundle\Security;

use AppBundle\Entity\SuccessStory;
use AppBundle\Model\PublishAware;
use Application\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SuccessStoryVoter extends Voter
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

        if (!$subject instanceof SuccessStory) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $successStory
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $successStory, TokenInterface $token)
    {
        $user = $token->getUser();

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($successStory, $user);
            case self::EDIT:
                return $this->canEdit($successStory, $user);
            case self::DELETE:
                return $this->canDelete($successStory, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param SuccessStory $story
     * @param $user
     * @return bool
     */
    private function canView(SuccessStory $story, $user)
    {
        return true;
    }

    /**
     * @param SuccessStory $story
     * @param $user
     * @return bool
     */
    private function canEdit(SuccessStory $story, $user)
    {
        if (!$user instanceof User){
            return false;
        }

        if ($user->isAdmin()){
            return true;
        }

        if ($user->getId() == $story->getUser()->getId()){
            return true;
        }

        return false;
    }

    /**
     * @param SuccessStory $story
     * @param $user
     * @return bool
     */
    public function canDelete(SuccessStory $story, $user)
    {
        return $this->canEdit($story, $user);
    }
}