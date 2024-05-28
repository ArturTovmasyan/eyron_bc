<?php
/**
 * Created by PhpStorm.
 * User: artur
 * Date: 05/05/16
 * Time: 17:12 PM
 */
namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Goal;
use AppBundle\Entity\UserGoal;
use AppBundle\Form\MergeGoalType;
use Application\CommentBundle\Entity\Thread;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CRUDController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @ParamConverter("goal", class="AppBundle:Goal")
     */
    public function mergeAction(Request $request, Goal $goal)
    {
        //get goalId
        $goalId = $goal->getId();
        
        // create form
        $form = $this->createForm(new MergeGoalType($goalId));

        //check if method post
        if ($request->isMethod('POST')) {

            //get entity manager
            $em = $this->get('doctrine')->getManager();

            // get data from request
            $form->handleRequest($request);

            if ($form->isValid() && $form->isSubmitted()) {
                
                //get merging goal id in form
                $mergingGoal = $form->get('goal')->getData();

                //get tag value in form
                $tagChecked = $form->get('tags')->getData();

                //get story value in form
                $storyChecked = $form->get('successStory')->getData();

                //get comment value in form
                $commentChecked = $form->get('comment')->getData();

                //get user value in form
                $userChecked = $form->get('user')->getData();

                //get merge goal
                $mergeGoalObject = $em->getRepository('AppBundle:Goal')->find($mergingGoal->getId());

                //merge goal author by roles
                $this->mergeGoalAuthor($goal, $em, $mergeGoalObject);

                //check if tag checked
                if ($tagChecked) {
                    $this->mergeTags($goal, $mergingGoal, $em, $mergeGoalObject);
                }

                //check if successStory checked
                if ($storyChecked) {
                    $this->mergeSuccessStory($goal, $em, $mergeGoalObject);
                }

                //check if comment checked
                if ($commentChecked) {
                    $this->mergeComments($goal, $em, $mergeGoalObject);
                }

                //check if user checked
                if ($userChecked) {
                    $this->mergeUsers($goal, $mergingGoal, $em, $mergeGoalObject);
                }

                //set goal id in merge goal
                $mergeGoalObject->setMergedGoalId($goalId);
                $em->persist($mergeGoalObject);

                //set goal archived
                $goal->setArchived(true);
                $em->persist($goal);

                $em->flush();

                //set flush messages
                $this->addFlash('sonata_flash_success', 'Goal id = '.$goalId.' has been success merged with id = '.$mergingGoal->getId().'');
                $this->addFlash('GoalMerge','Goal merge from Web');

                return new RedirectResponse($this->admin->generateUrl('list'));
            }
        }

        return $this->render('AppBundle:Admin:goal_merge.html.twig', array(
          'goal' => $goal, 'form' => $form->createView(),
        ));
    }

    /**
     * This function is used to merge goal comments
     *
     * @param $goal
     * @param $em
     * @param $mergeGoalObject
     */
    public function mergeComments($goal, $em, $mergeGoalObject)
    {
        //get thread id
        $threadId = 'goal_'.$goal->getSlug();

        // get goal comments
        $goalComments = $em->getRepository("ApplicationCommentBundle:Comment")->findCommentsByGoalId($threadId);

        //check if goal comments exist
        if(count($goalComments) > 0){

            //get first comment in goal
            $goalComment = reset($goalComments);

            //get goal old thread for remove
            $goalOldThread = $goalComment->getThread();

            $mergeGoalThreadId = 'goal_'.$mergeGoalObject->getSlug();

            //get merged goal thread
            $mergedGoalOldThread = $em->getRepository("ApplicationCommentBundle:Comment")->findThreadByGoalId($mergeGoalThreadId);

            //check if merged goal comments exist
            if(count($mergedGoalOldThread ) > 0){

                //get merged goal thread
                $mergedGoalThread = reset($mergedGoalOldThread);
            }
            else{

                //create new thread for merged goal comments
                $mergedGoalThread = new Thread();
                $mergedGoalThread->setId($mergeGoalThreadId);
                $em->persist($mergedGoalThread);
            }

            foreach($goalComments as $goalComment){

                //set merged goal thread in goal comment
                $goalComment->setThread($mergedGoalThread);
                $em->persist($goalComment);
            }

            //remove goal old thread
            $em->remove($goalOldThread);
        }
    }


    /**
     * This function is used to merge goal success story
     *
     * @param $goal
     * @param $em
     * @param $mergeGoalObject
     */
    public function mergeSuccessStory($goal, $em, $mergeGoalObject)
    {
        //get goal success story
        $goalSuccessStory = $goal->getSuccessStories();

        foreach($goalSuccessStory as $story)
        {
            //add success story in merged goal
            $mergeGoalObject->addSuccessStory($story);
            $em->persist($mergeGoalObject);
        }
    }

    /**
     * This function is used to merge goal author by user roles
     *
     * @param $goal
     * @param $em
     * @param $mergeGoalObject
     */
    public function mergeGoalAuthor($goal, $em, $mergeGoalObject)
    {
        //get goal author
        $goalAuthor = $goal->getAuthor();

        //get merge goal author
        $mergeGoalAuthor = $mergeGoalObject->getAuthor();

        if($goalAuthor && $mergeGoalAuthor) {

            //if goalAuthor is admin and merge goal author is not
            if($goalAuthor->isAdmin() && !$mergeGoalAuthor->isAdmin()) {
                return;
            }

            //if goalAuthor and mergeGoalAuthor is admin
            if($goalAuthor->isAdmin() && $mergeGoalAuthor->isAdmin()) {
                return;
            }

            //if goalAuthor is not admin and merge goal author is yes
            if(!$goalAuthor->isAdmin() && $mergeGoalAuthor->isAdmin()) {

                $mergeGoalObject->setAuthor($goalAuthor);
                $em->persist($mergeGoalObject);
                return;
            }

            //if goalAuthor and mergeGoalAuthor is not admin
            if(!$goalAuthor->isAdmin() && !$mergeGoalAuthor->isAdmin()) {

                $mergeGoalObject->setAuthor($goalAuthor);
                $em->persist($mergeGoalObject);
                return;
            }
        }
        else{

            //check if merge goal don't have author
            if($goalAuthor && !$mergeGoalAuthor) {

                $mergeGoalObject->setAuthor($goalAuthor);
                $em->persist($mergeGoalObject);
                return;
            }

            //check if goal don't have author
            if(!$goalAuthor && $mergeGoalAuthor) {
                return;
            }

            //check if goals don't have any author
            if(!$goalAuthor && !$mergeGoalAuthor) {
                return;
            }
        }
    }

    /**
     * This function is used to merge goal tags
     *
     * @param $goal
     * @param $mergingGoal
     * @param $em
     * @param $mergeGoalObject
     */
    public function mergeTags($goal, $mergingGoal, $em, $mergeGoalObject)
    {
        //get goal tags ids
        $goalTagIds = $this->getTagsIdsByGoal($goal);

        //get merging goal tags ids
        $mergeGoalTagIds = $this->getTagsIdsByGoal($mergingGoal);

        //get user ids for get userGoals
        $tagsIds = array_diff($goalTagIds, $mergeGoalTagIds);

        //get tags for merge
        $mergeGoalTags = $em->getRepository('AppBundle:Tag')->findTagsByIds($tagsIds, $goal->getId());

        foreach($mergeGoalTags as $mergeGoalTag)
        {
            //add success story in merged goal
            $mergeGoalObject->addTag($mergeGoalTag);
            $em->persist($mergeGoalObject);
        }

        //get all tags for old goal
        $oldGoalTags = $goal->getTags();

        foreach($oldGoalTags as $oldGoalTag)
        {
            //remove old goal user goals
            $goal->removeTag($oldGoalTag);
            $em->persist($goal);
        }
    }

    /**
     * This function is used to merge listed and done, activities users for goal
     *
     * @param $goal
     * @param $mergingGoal
     * @param $em
     * @param $mergeGoalObject
     */
    public function mergeUsers($goal, $mergingGoal, $em, $mergeGoalObject)
    {
        //get user ids by user goal
        $goalUserIds = $this->getUserIdsByUserGoals($goal);

        //get user ids by merging user goal
        $mergeGoalUserIds = $this->getUserIdsByUserGoals($mergingGoal);

        //get user ids for get userGoals
        $userIds = array_diff($goalUserIds, $mergeGoalUserIds);

        //get user goals for merging
        $mergeUserGoals = $em->getRepository('AppBundle:UserGoal')->findUserGoalsByUserId($userIds, $goal->getId());

        //get all userGoals for old goal
        $oldGoalUserGoals = $goal->getUserGoal();

        foreach($oldGoalUserGoals as $oldGoalUserGoal)
        {
            //remove old goal user goals
            $goal->removeUserGoal($oldGoalUserGoal);
            $em->persist($goal);

            //remove old goal user goals
            $em->remove($oldGoalUserGoal);
        }

        //check if $mergeUserGoals exist
        if(count($mergeUserGoals) > 0) {

            foreach($mergeUserGoals as $mergeUserGoal)
            {
                //add success story in merged goal
                $mergeGoalObject->addUserGoal($mergeUserGoal);
                $em->persist($mergeGoalObject);
            }
        }

        $em->flush();
    }

    /**
     * This function is used to get user ids by user goals
     *
     * @param $data
     * @return array
     */
    public function getUserIdsByUserGoals($data)
    {
        $data->getUserGoal()->count();

        //get all user goals
        $userGoals = $data->getUserGoal();

        //set default array
        $userIds = array();

        foreach($userGoals as $userGoal)
        {
            $userIds[] = $userGoal->getUser()->getId();
        }

        return $userIds;
    }

    /**
     * This function is used to get tags ids by goal
     *
     * @param $data
     * @return array
     */
    public function getTagsIdsByGoal($data)
    {
        $data->getTags()->count();

        //get all goal tags
        $goalTags = $data->getTags();

        //set default array
        $tagsIds = array();

        foreach($goalTags as $goalTag)
        {
            $tagsIds[] = $goalTag->getId();
        }

        return $tagsIds;
    }

    /**
     * Edit action.
     *
     * @param int|string|null $id
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function editAction($id = null)
    {
        //get entity manager
        $em = $this->get('doctrine')->getManager();

        //disable goal archived filters
        $em->getFilters()->disable('archived_goal_filter');

        //get parent edit action
        $result =  parent::editAction($id = null);

        return $result;
    }

    /**
     * Show action.
     *
     * @param int|string|null $id
     *
     * @return Response
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function showAction($id = null)
    {
        //get entity manager
        $em = $this->get('doctrine')->getManager();

        //disable goal archived filters
        $em->getFilters()->disable('archived_goal_filter');

        //get parent edit action
        $result =  parent::showAction($id = null);

        return $result;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @param Request $request
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_GOD')")
     */
    public function sendMessageAction(Request $request)
    {
        //get url parameters in request
        $userId = $request->query->get('userId');
        $deviceId = $request->query->get('deviceId');
        $mobileOS = $request->query->get('mobileOS');

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get put notification service
        $sendNoteService = $this->container->get('bl_put_notification_service');

        //get user by id
        $user = $em->getRepository('ApplicationUserBundle:User')->find($userId);

        $data = [];

        // get referer
//        $referer = $request->server->get('HTTP_REFERER');

        // generate url
//        $url =
//            $referer ?
//                $referer :
//                $this->generateUrl('homepage');

        //check if user haven`t any goals
        if ($user) {
            $data = $sendNoteService->sendTestMassage($user, $deviceId, $mobileOS);
            $this->addFlash('success', 'You sand Test Message successfully');
        }else {
            $this->addFlash('error', 'User Not Found');
        }

        $data[$deviceId] = true;

        return  $this->redirectToRoute('admin-user_show', ['id' => $userId, 'data' => $data]);
    }
}