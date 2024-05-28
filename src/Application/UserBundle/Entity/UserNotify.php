<?php
namespace Application\UserBundle\Entity;

use AppBundle\Services\UserNotifyService;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_notify")
 */
class UserNotify
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="is_comment_on_goal_notify", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isCommentOnGoalNotify = true;

    /**
     * @ORM\Column(name="is_comment_on_idea_notify", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isCommentOnIdeaNotify = true;

    /**
     * @ORM\Column(name="is_comment_on_goal_push", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isCommentOnGoalPush = true;

    /**
     * @ORM\Column(name="is_comment_on_idea_push", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isCommentOnIdeaPush = true;

    /**
     * @ORM\Column(name="is_success_story_on_goal_notify", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isSuccessStoryOnGoalNotify = true;

    /**
     * @ORM\Column(name="is_success_story_on_idea_notify", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isSuccessStoryOnIdeaNotify = true;

    /**
     * @ORM\Column(name="is_success_story_on_goal_push", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isSuccessStoryOnGoalPush = true;

    /**
     * @ORM\Column(name="is_success_story_on_idea_push", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isSuccessStoryOnIdeaPush = true;


    /**
     * @ORM\Column(name="is_success_story_like_notify", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isSuccessStoryLikeNotify = true;

    /**
     * @ORM\Column(name="is_success_story_like_push", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isSuccessStoryLikePush = true;

    /**
     * @ORM\Column(name="is_goal_publish_notify", type="boolean")
     * @var
     * @Groups({"settings"})
     */
    private $isGoalPublishNotify = true;

    /**
     * @ORM\Column(name="is_goal_publish_push", type="boolean" )
     * @var
     * @Groups({"settings"})
     */
    private $isGoalPublishPush = true;

    /**
     * @ORM\Column(name="is_comment_reply_notify", type="boolean" )
     * @var
     * @Groups({"settings"})
     */
    private $isCommentReplyNotify = true;

    /**
     * @ORM\Column(name="is_comment_reply_push", type="boolean" )
     * @var
     * @Groups({"settings"})
     */
    private $isCommentReplyPush = true;

    /**
     * @ORM\Column(name="is_deadline_exp_notify", type="boolean" )
     * @var
     * @Groups({"settings"})
     */
    private $isDeadlineExpNotify = true;

    /**
     * @ORM\Column(name="is_deadline_exp_push", type="boolean" )
     * @var
     * @Groups({"settings"})
     */
    private $isDeadlineExpPush = true;

    /**
     * @ORM\Column(name="is_new_goal_friend_notify", type="boolean" )
     * @var
     * @Groups({"settings"})
     */
    private $isNewGoalFriendNotify = true;

    /**
     * @ORM\Column(name="is_new_goal_friend_push", type="boolean" )
     * @var
     * @Groups({"settings"})
     */
    private $isNewGoalFriendPush = true;

    /**
     * @ORM\Column(name="is_new_idea_notify", type="boolean" )
     * @var
     * @Groups({"settings"})
     */
    private $isNewIdeaNotify = true;

    /**
     * @ORM\Column(name="is_new_idea_push", type="boolean" )
     * @var
     * @Groups({"settings"})
     */
    private $isNewIdeaPush = true;

    /**
     * @ORM\OneToOne(targetEntity="Application\UserBundle\Entity\User", mappedBy="userNotifySettings")
     */
    private $user;

    /**
     * Get id
     *
     * @return integer
     */
    public function notifySwitchesOff()
    {
        $this->setIsCommentOnGoalNotify(false);
        $this->setIsCommentOnIdeaNotify(false);
        $this->setIsCommentOnGoalPush(false);
        $this->setIsCommentOnIdeaPush(false);
        $this->setIsSuccessStoryOnGoalNotify(false);
        $this->setIsSuccessStoryOnIdeaNotify(false);
        $this->setIsSuccessStoryOnGoalPush(false);
        $this->setIsSuccessStoryOnIdeaPush(false);
        $this->setIsSuccessStoryLikeNotify(false);
        $this->setIsSuccessStoryLikePush(false);
        $this->setIsGoalPublishNotify(false);
        $this->setIsGoalPublishPush(false);
        $this->setIsCommentReplyNotify(false);
        $this->setIsDeadlineExpNotify(false);
        $this->setIsDeadlineExpPush(false);
        $this->setIsNewIdeaNotify(false);
        $this->setIsNewIdeaPush(false);
        $this->setIsNewGoalFriendNotify(false);
        $this->setIsNewGoalFriendPush(false);
        $this->setIsCommentReplyPush(false);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isCommentOnGoalNotify
     *
     * @param boolean $isCommentOnGoalNotify
     *
     * @return UserNotify
     */
    public function setIsCommentOnGoalNotify($isCommentOnGoalNotify)
    {
        $this->isCommentOnGoalNotify = $isCommentOnGoalNotify;

        return $this;
    }

    /**
     * Get isCommentOnGoalNotify
     *
     * @return boolean
     */
    public function getIsCommentOnGoalNotify()
    {
        return $this->isCommentOnGoalNotify;
    }

    /**
     * Set isCommentOnIdeaNotify
     *
     * @param boolean $isCommentOnIdeaNotify
     *
     * @return UserNotify
     */
    public function setIsCommentOnIdeaNotify($isCommentOnIdeaNotify)
    {
        $this->isCommentOnIdeaNotify = $isCommentOnIdeaNotify;

        return $this;
    }

    /**
     * Get isCommentOnIdeaNotify
     *
     * @return boolean
     */
    public function getIsCommentOnIdeaNotify()
    {
        return $this->isCommentOnIdeaNotify;
    }

    /**
     * Set isCommentOnGoalPush
     *
     * @param boolean $isCommentOnGoalPush
     *
     * @return UserNotify
     */
    public function setIsCommentOnGoalPush($isCommentOnGoalPush)
    {
        $this->isCommentOnGoalPush = $isCommentOnGoalPush;

        return $this;
    }

    /**
     * Get isCommentOnGoalPush
     *
     * @return boolean
     */
    public function getIsCommentOnGoalPush()
    {
        return $this->isCommentOnGoalPush;
    }

    /**
     * Set isCommentOnIdeaPush
     *
     * @param boolean $isCommentOnIdeaPush
     *
     * @return UserNotify
     */
    public function setIsCommentOnIdeaPush($isCommentOnIdeaPush)
    {
        $this->isCommentOnIdeaPush = $isCommentOnIdeaPush;

        return $this;
    }

    /**
     * Get isCommentOnIdeaPush
     *
     * @return boolean
     */
    public function getIsCommentOnIdeaPush()
    {
        return $this->isCommentOnIdeaPush;
    }

    /**
     * Set isSuccessStoryOnGoalNotify
     *
     * @param boolean $isSuccessStoryOnGoalNotify
     *
     * @return UserNotify
     */
    public function setIsSuccessStoryOnGoalNotify($isSuccessStoryOnGoalNotify)
    {
        $this->isSuccessStoryOnGoalNotify = $isSuccessStoryOnGoalNotify;

        return $this;
    }

    /**
     * Get isSuccessStoryOnGoalNotify
     *
     * @return boolean
     */
    public function getIsSuccessStoryOnGoalNotify()
    {
        return $this->isSuccessStoryOnGoalNotify;
    }

    /**
     * Set isSuccessStoryOnIdeaNotify
     *
     * @param boolean $isSuccessStoryOnIdeaNotify
     *
     * @return UserNotify
     */
    public function setIsSuccessStoryOnIdeaNotify($isSuccessStoryOnIdeaNotify)
    {
        $this->isSuccessStoryOnIdeaNotify = $isSuccessStoryOnIdeaNotify;

        return $this;
    }

    /**
     * Get isSuccessStoryOnIdeaNotify
     *
     * @return boolean
     */
    public function getIsSuccessStoryOnIdeaNotify()
    {
        return $this->isSuccessStoryOnIdeaNotify;
    }

    /**
     * Set isSuccessStoryOnGoalPush
     *
     * @param boolean $isSuccessStoryOnGoalPush
     *
     * @return UserNotify
     */
    public function setIsSuccessStoryOnGoalPush($isSuccessStoryOnGoalPush)
    {
        $this->isSuccessStoryOnGoalPush = $isSuccessStoryOnGoalPush;

        return $this;
    }

    /**
     * Get isSuccessStoryOnGoalPush
     *
     * @return boolean
     */
    public function getIsSuccessStoryOnGoalPush()
    {
        return $this->isSuccessStoryOnGoalPush;
    }

    /**
     * Set isSuccessStoryOnIdeaPush
     *
     * @param boolean $isSuccessStoryOnIdeaPush
     *
     * @return UserNotify
     */
    public function setIsSuccessStoryOnIdeaPush($isSuccessStoryOnIdeaPush)
    {
        $this->isSuccessStoryOnIdeaPush = $isSuccessStoryOnIdeaPush;

        return $this;
    }

    /**
     * Get isSuccessStoryOnIdeaPush
     *
     * @return boolean
     */
    public function getIsSuccessStoryOnIdeaPush()
    {
        return $this->isSuccessStoryOnIdeaPush;
    }

    /**
     * Set isSuccessStoryLikeNotify
     *
     * @param boolean $isSuccessStoryLikeNotify
     *
     * @return UserNotify
     */
    public function setIsSuccessStoryLikeNotify($isSuccessStoryLikeNotify)
    {
        $this->isSuccessStoryLikeNotify = $isSuccessStoryLikeNotify;

        return $this;
    }

    /**
     * Get isSuccessStoryLikeNotify
     *
     * @return boolean
     */
    public function getIsSuccessStoryLikeNotify()
    {
        return $this->isSuccessStoryLikeNotify;
    }

    /**
     * Set isSuccessStoryLikePush
     *
     * @param boolean $isSuccessStoryLikePush
     *
     * @return UserNotify
     */
    public function setIsSuccessStoryLikePush($isSuccessStoryLikePush)
    {
        $this->isSuccessStoryLikePush = $isSuccessStoryLikePush;

        return $this;
    }

    /**
     * Get isSuccessStoryLikePush
     *
     * @return boolean
     */
    public function getIsSuccessStoryLikePush()
    {
        return $this->isSuccessStoryLikePush;
    }

    /**
     * Set isGoalPublishNotify
     *
     * @param boolean $isGoalPublishNotify
     *
     * @return UserNotify
     */
    public function setIsGoalPublishNotify($isGoalPublishNotify)
    {
        $this->isGoalPublishNotify = $isGoalPublishNotify;

        return $this;
    }

    /**
     * Get isGoalPublishNotify
     *
     * @return boolean
     */
    public function getIsGoalPublishNotify()
    {
        return $this->isGoalPublishNotify;
    }

    /**
     * Set isGoalPublishPush
     *
     * @param boolean $isGoalPublishPush
     *
     * @return UserNotify
     */
    public function setIsGoalPublishPush($isGoalPublishPush)
    {
        $this->isGoalPublishPush = $isGoalPublishPush;

        return $this;
    }

    /**
     * Get isGoalPublishPush
     *
     * @return boolean
     */
    public function getIsGoalPublishPush()
    {
        return $this->isGoalPublishPush;
    }

    /**
     * Set isCommentReplyNotify
     *
     * @param boolean $isCommentReplyNotify
     *
     * @return UserNotify
     */
    public function setIsCommentReplyNotify($isCommentReplyNotify)
    {
        $this->isCommentReplyNotify = $isCommentReplyNotify;

        return $this;
    }

    /**
     * Get isCommentReplyNotify
     *
     * @return boolean
     */
    public function getIsCommentReplyNotify()
    {
        return $this->isCommentReplyNotify;
    }

    /**
     * Set isDeadlineExpNotify
     *
     * @param boolean $isDeadlineExpNotify
     *
     * @return UserNotify
     */
    public function setIsDeadlineExpNotify($isDeadlineExpNotify)
    {
        $this->isDeadlineExpNotify = $isDeadlineExpNotify;

        return $this;
    }

    /**
     * Get isDeadlineExpNotify
     *
     * @return boolean
     */
    public function getIsDeadlineExpNotify()
    {
        return $this->isDeadlineExpNotify;
    }

    /**
     * Set isDeadlineExpPush
     *
     * @param boolean $isDeadlineExpPush
     *
     * @return UserNotify
     */
    public function setIsDeadlineExpPush($isDeadlineExpPush)
    {
        $this->isDeadlineExpPush = $isDeadlineExpPush;

        return $this;
    }

    /**
     * Get isDeadlineExpPush
     *
     * @return boolean
     */
    public function getIsDeadlineExpPush()
    {
        return $this->isDeadlineExpPush;
    }

    /**
     * Set isNewIdeaNotify
     *
     * @param boolean $isNewIdeaNotify
     *
     * @return UserNotify
     */
    public function setIsNewIdeaNotify($isNewIdeaNotify)
    {
        $this->isNewIdeaNotify = $isNewIdeaNotify;

        return $this;
    }

    /**
     * Get isNewIdeaNotify
     *
     * @return boolean
     */
    public function getIsNewIdeaNotify()
    {
        return $this->isNewIdeaNotify;
    }

    /**
     * Set isNewIdeaPush
     *
     * @param boolean $isNewIdeaPush
     *
     * @return UserNotify
     */
    public function setIsNewIdeaPush($isNewIdeaPush)
    {
        $this->isNewIdeaPush = $isNewIdeaPush;

        return $this;
    }

    /**
     * Get isNewIdeaPush
     *
     * @return boolean
     */
    public function getIsNewIdeaPush()
    {
        return $this->isNewIdeaPush;
    }

    /**
     * Set user
     *
     * @param \Application\UserBundle\Entity\User $user
     *
     * @return UserNotify
     */
    public function setUser(\Application\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Application\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set isNewGoalFriendNotify
     *
     * @param boolean $isNewGoalFriendNotify
     *
     * @return UserNotify
     */
    public function setIsNewGoalFriendNotify($isNewGoalFriendNotify)
    {
        $this->isNewGoalFriendNotify = $isNewGoalFriendNotify;

        return $this;
    }

    /**
     * Get isNewGoalFriendNotify
     *
     * @return boolean
     */
    public function getIsNewGoalFriendNotify()
    {
        return $this->isNewGoalFriendNotify;
    }

    /**
     * Set isNewGoalFriendPush
     *
     * @param boolean $isNewGoalFriendPush
     *
     * @return UserNotify
     */
    public function setIsNewGoalFriendPush($isNewGoalFriendPush)
    {
        $this->isNewGoalFriendPush = $isNewGoalFriendPush;

        return $this;
    }

    /**
     * Get isNewGoalFriendPush
     *
     * @return boolean
     */
    public function getIsNewGoalFriendPush()
    {
        return $this->isNewGoalFriendPush;
    }

    /**
     * Set isCommentReplyPush
     *
     * @param boolean $isCommentReplyPush
     *
     * @return UserNotify
     */
    public function setIsCommentReplyPush($isCommentReplyPush)
    {
        $this->isCommentReplyPush = $isCommentReplyPush;

        return $this;
    }

    /**
     * Get isCommentReplyPush
     *
     * @return boolean
     */
    public function getIsCommentReplyPush()
    {
        return $this->isCommentReplyPush;
    }

    /**
     * @param $type
     * @return bool
     */
    public function mustEmailNotify($type)
    {
        $result = false;

        switch ($type){
            case UserNotifyService::COMMENT_GOAL:
                $result = $this->getIsCommentOnGoalNotify();
                break;
            case UserNotifyService::COMMENT_IDEA:
                $result = $this->getIsCommentOnIdeaNotify();
                break;
            case UserNotifyService::SUCCESS_STORY_GOAL:
                $result = $this->getIsSuccessStoryOnGoalNotify();
                break;
            case UserNotifyService::SUCCESS_STORY_IDEA:
                $result = $this->getIsSuccessStoryOnIdeaNotify();
                break;
            case UserNotifyService::SUCCESS_STORY_LIKE:
                $result = $this->getIsSuccessStoryLikeNotify();
                break;
            case UserNotifyService::PUBLISH_GOAL:
                $result = $this->getIsGoalPublishNotify();
                break;
            case UserNotifyService::REPLY_COMMENT:
                $result = $this->getIsCommentReplyNotify();
                break;
            case UserNotifyService::DEADLINE:
                $result = $this->getIsDeadlineExpNotify();
                break;
            case UserNotifyService::NEW_GOAL_FRIEND:
                $result = $this->getIsNewGoalFriendNotify();
                break;
            case UserNotifyService::NEW_IDEA:
                $result = $this->getIsNewIdeaNotify();
                break;
        }

        return $result;
    }

    /**
     * @param $type
     * @return bool
     */
    public function mustPushedNotify($type)
    {
        $result = false;

        switch ($type){
            case UserNotifyService::COMMENT_GOAL:
                $result = $this->getIsCommentOnGoalPush();
                break;
            case UserNotifyService::COMMENT_IDEA:
                $result = $this->getIsCommentOnIdeaPush();
                break;
            case UserNotifyService::SUCCESS_STORY_GOAL:
                $result = $this->getIsSuccessStoryOnGoalPush();
                break;
            case UserNotifyService::SUCCESS_STORY_IDEA:
                $result = $this->getIsSuccessStoryOnIdeaPush();
                break;
            case UserNotifyService::SUCCESS_STORY_LIKE:
                $result = $this->getIsSuccessStoryLikePush();
                break;
            case UserNotifyService::PUBLISH_GOAL:
                $result = $this->getIsGoalPublishPush();
                break;
            case UserNotifyService::REPLY_COMMENT:
                $result = $this->getIsCommentReplyPush();
                break;
            case UserNotifyService::DEADLINE:
                $result = $this->getIsDeadlineExpPush();
                break;
            case UserNotifyService::NEW_GOAL_FRIEND:
                $result = $this->getIsNewGoalFriendPush();
                break;
            case UserNotifyService::NEW_IDEA:
                $result = $this->getIsNewIdeaPush();
                break;
        }

        return $result;
    }
}
