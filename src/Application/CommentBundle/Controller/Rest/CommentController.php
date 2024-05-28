<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/12/16
 * Time: 2:57 PM
 */
namespace Application\CommentBundle\Controller\Rest;

use AppBundle\Entity\Blog;
use AppBundle\Entity\Goal;
use AppBundle\Entity\GoalImage;
use Application\CommentBundle\Entity\Comment;
use Application\CommentBundle\Entity\Thread;
use Application\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Rest\RouteResource("Comment")
 * @Rest\Prefix("/api/v1.0")
 */
class CommentController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Comment",
     *  description="This function is used to create or update userGoal",
     *  statusCodes={
     *         201="Returned when comment was added",
     *         401="Returned when user not found",
     *         400="Body is empty or parent's thread isn't given parent"
     *     },
     *  parameters={
     *      {"name"="commentBody", "dataType"="string", "required"=true, "description"="Comment body"},
     * }
     * )
     *
     * @Rest\PUT("/comments/{goal}/{parentComment}", requirements={"goal"="\d+", "parentComment"="\d+"}, defaults={"parentComment"=null}, name="put_comment", options={"method_prefix"=false})
     * @Rest\PUT("/goals/{goal}/comment", requirements={"goal"="\d+", "parentComment"="\d+"}, name="mobile_put_comment", options={"method_prefix"=false})
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("goal", class="AppBundle:Goal", options={"repository_method" = "findGoalWithAuthor"})
     * @Rest\View(serializerGroups={"comment", "comment_children", "comment_author", "tiny_user"})
     *
     * @param Request $request
     * @param Goal $goal
     * @param Comment $parentComment
     * @return Response
     */
    public function putAction(Request $request, Goal $goal, Comment $parentComment = null)
    {
        return $this->container->get("application.comment")
            ->putComment($request, $goal, $this->getUser(), $parentComment);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Comment",
     *  description="This function is used to put comment in blog",
     *  statusCodes={
     *         201="Returned when comment was added",
     *         401="Returned when user not found",
     *         400="Body is empty or parent's thread isn't given parent"
     *     },
     *  parameters={
     *      {"name"="commentBody", "dataType"="string", "required"=true, "description"="Comment body"},
     * }
     * )
     *
     * @Rest\PUT("/blog-comment/{blog}/{parentComment}", requirements={"goal"="\d+", "parentComment"="\d+"}, defaults={"parentComment"=null}, name="put_blog_comment", options={"method_prefix"=false})
     * @Rest\PUT("/blog/{blog}/comment", requirements={"goal"="\d+", "parentComment"="\d+"}, name="mobile_put_blog_comment", options={"method_prefix"=false})
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("blog", class="AppBundle:Blog")
     * @Rest\View(serializerGroups={"comment", "comment_children", "comment_author", "tiny_user"})
     *
     * @param Request $request
     * @param Blog $blog
     * @param Comment $parentComment
     * @return Response
     */
    public function putBlogAction(Request $request, Blog $blog, Comment $parentComment = null)
    {
        return $this->container->get("application.comment")
            ->putBlogComment($request, $blog, $this->getUser(), $parentComment);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Comment",
     *  description="This function is used to get comments by thread id",
     *  statusCodes={
     *         200="Returned when all ok",
     *         404="Returned when thread not found",
     *         401="Returned when user not found"
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"comment", "comment_children", "comment_author", "tiny_user"})
     * @Rest\Get("/comments/{threadId}/{first}/{count}", requirements={"first"="\d+", "count"="\d+"}, defaults={"first"=null, "count"=null}, name="get_comment", options={"method_prefix"=false})
     *
     * @param Thread $threadId
     * @param null $first
     * @param null $count
     * @return array
     */
    public function getAction($threadId, $first = null, $count = null)
    {
        $em = $this->getDoctrine()->getManager();
        $thread = $em->getRepository('ApplicationCommentBundle:Thread')->find($threadId);
        if (is_null($thread)){
            return [];
        }

        $comments = $em->getRepository('ApplicationCommentBundle:Comment')->findThreadComments($thread->getId(), $first, $count);

        return $comments;
    }
}