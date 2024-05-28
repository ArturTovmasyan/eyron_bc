<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/24/16
 * Time: 12:21 PM
 */

namespace Application\CommentBundle\Services;

use AppBundle\Entity\Blog;
use AppBundle\Entity\Goal;
use AppBundle\Services\AbstractProcessService;
use AppBundle\Services\UserNotifyService;
use Application\CommentBundle\Entity\Comment;
use Application\CommentBundle\Entity\Thread;
use Application\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class CommentService
 * @package Application\CommentBundle\Services
 */
class CommentService extends AbstractProcessService
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @param Request $request
     * @param Goal $goal
     * @param User $user
     * @param Comment|null $parentComment
     * @return Comment
     * @throws \Throwable
     */
    public function putComment(Request $request, Goal $goal, User $user,  Comment $parentComment = null)
    {
        if($request->getContentType() == 'application/json' || $request->getContentType() == 'json'){
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        $em = $this->container->get('doctrine')->getManager();
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();

        $threadId = 'goal_' . $goal->getSlug();
        $thread = $em->getRepository('ApplicationCommentBundle:Thread')->find($threadId);
        if (is_null($thread)){
            $thread = new Thread();
            $thread->setId($threadId);

            $em->persist($thread);
        }

        if (!is_null($parentComment)){
            if ($parentComment->getThread()->getId() != $thread->getId()){
                throw new HttpException(Response::HTTP_BAD_REQUEST);
            }
        }

        $body = $request->get('commentBody', null);
        if (is_null($body)){
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Body can not be empty');
        }

        // get user notify service
        $userNotify = $this->container->get('user_notify');


        $comment = new Comment();
        $comment->setThread($thread);
        $comment->setAuthor($user);
        $comment->setBody($body);
        $comment->setParent($parentComment);
        $thread->addComment($comment);
        $em->persist($comment);


        $this->container->get('request_stack')->getCurrentRequest()->getSession()
            ->getFlashBag()
            ->set('comments','Add comment from Web');

        $em->flush();

        // check if parent comment
        if($parentComment){
            $userNotify->sendNotifyToUser($parentComment->getAuthor(),
                UserNotifyService::REPLY_COMMENT,
                ['goalId'=> $goal->getId()]);
        }else{
            if($goal->hasAuthorForNotify($user->getId())) {
                $this->container->get('user_notify')->sendNotifyToUser($goal->getAuthor(),
                    UserNotifyService::COMMENT_GOAL,
                    ['goalId'=> $goal->getId(), 'commentId' => $comment->getId()]);
            }
        }

        $this->runAsProcess('application.comment', 'sendNotification',
            array($goal->getId(), $user->getId(), $parentComment ? $parentComment->getId() : null ));

        return $comment;

    }


    /**
     * @param Request $request
     * @param Blog $blog
     * @param User $user
     * @param Comment|null $parentComment
     * @return Comment
     * @throws \Throwable
     */
    public function putBlogComment(Request $request, Blog $blog, User $user,  Comment $parentComment = null)
    {
        if($request->getContentType() == 'application/json' || $request->getContentType() == 'json'){
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        $em = $this->container->get('doctrine')->getManager();
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();

        $threadId = 'blog_' . $blog->getSlug();
        $thread = $em->getRepository('ApplicationCommentBundle:Thread')->find($threadId);
        if (is_null($thread)){
            $thread = new Thread();
            $thread->setId($threadId);

            $em->persist($thread);
        }

        if (!is_null($parentComment)){
            if ($parentComment->getThread()->getId() != $thread->getId()){
                throw new HttpException(Response::HTTP_BAD_REQUEST);
            }
        }

        $body = $request->get('commentBody', null);
        if (is_null($body)){
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Body can not be empty');
        }

        $comment = new Comment();
        $comment->setThread($thread);
        $comment->setAuthor($user);
        $comment->setBody($body);
        $comment->setParent($parentComment);
        $thread->addComment($comment);
        $em->persist($comment);


        $this->container->get('request_stack')->getCurrentRequest()->getSession()
            ->getFlashBag()
            ->set('comments','Add comment from Web');

        $em->flush();

        return $comment;

    }

    /**
     * @param $goalId
     * @param $userId
     * @param $parentCommentId
     * @throws \Throwable
     */
    public function sendNotification($goalId, $userId, $parentCommentId = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $goal = $em->getRepository("AppBundle:Goal")->find($goalId);
        $user = $em->getRepository("ApplicationUserBundle:User")->find($userId);
        $parentComment = $parentCommentId ?
            $em->getRepository("ApplicationCommentBundle:Comment")->find($parentCommentId) : null;

        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();

        $authorId = $goal->getAuthor() ? $goal->getAuthor()->getId() : null;

        $importantAddedUsers = $em->getRepository('AppBundle:Goal')->findImportantAddedUsers($goalId, $authorId);
        $link = $this->container->get('router')->generate('inner_goal', ['slug' => $goal->getSlug()]);
        $body = $this->container->get('translator')->trans(is_null($parentComment) ? 'notification.important_goal_comment' : 'notification.important_goal_reply', [], null, 'en');
        $finFollowerBody = $this->container->get('translator')->trans('notification.following_comment', [], null, 'en');

        // finFollowers user
        $followers = $em->getRepository('ApplicationUserBundle:User')->findMyFollowers($user->getId());
        $this->container->get('bl_notification')->sendNotification($user, $link, $goal->getId(), $body, $importantAddedUsers);
        $this->container->get('bl_notification')->sendNotification($user, $link, $goal->getId(), $finFollowerBody, $followers);

        //check if goal author is not admin and not null
        if($goal && $goal->hasAuthorForNotify($user->getId())) {

            //Send notification to goal author
            $body = $this->container->get('translator')->trans(is_null($parentComment) ? 'notification.comment' : 'notification.reply', [], null, 'en');
            $this->container->get('bl_notification')->sendNotification($user, $link, $goal->getId(), $body, $goal->getAuthor());
        }

    }


}
