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
use Application\CommentBundle\Entity\Comment;
use Application\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
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

        if (!$subject instanceof Comment) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $comment
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $comment, TokenInterface $token)
    {
        $user = $token->getUser();

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($comment, $user);
            case self::EDIT:
                return $this->canEdit($comment, $user);
            case self::DELETE:
                return $this->canDelete($comment, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param Comment $comment
     * @param $user
     * @return bool
     */
    private function canView(Comment $comment, $user)
    {
        return ($user instanceof User);
    }

    /**
     * @param Comment $comment
     * @param $user
     * @return bool
     */
    private function canEdit(Comment $comment, $user)
    {
        if (!$user instanceof User){
            return false;
        }

        if ($user->isAdmin()){
            return true;
        }

        if ($user->getId() == $comment->getAuthor()->getId()){
            return true;
        }

        return false;
    }

    /**
     * @param Comment $comment
     * @param $user
     * @return bool
     */
    public function canDelete(Comment $comment, $user)
    {
        return $this->canEdit($comment, $user);
    }
}