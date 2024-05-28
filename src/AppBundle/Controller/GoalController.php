<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/10/15
 * Time: 9:53 AM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Goal;
use AppBundle\Entity\Tag;
use AppBundle\Entity\UserGoal;
use AppBundle\Form\GoalType;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;


/**
 * @Route("/")
 *
 * Class GoalController
 * @package AppBundle\Controller
 */
class GoalController extends Controller
{
    const STAGE_URL = 'http://stage.bucketlist127.com/';
    const STAGE_CACHE_PREFIX = '-stage';
    const PROD_CACHE_PREFIX = '-prod';

    /**
     * @Route("goal/add-modal", name="add_modal")
     * @return array
     */
    public function addModalAction()
    {
        // create filter
        $filters = array(
            UserGoal::NOT_URGENT_IMPORTANT => 'filters.import_not_urgent',
            UserGoal::URGENT_IMPORTANT => 'filters.import_urgent',
            UserGoal::NOT_URGENT_NOT_IMPORTANT => 'filters.not_import_not_urgent',
            UserGoal::URGENT_NOT_IMPORTANT => 'filters.not_import_urgent',
        );

        return $this->render('AppBundle:Goal:addToMe.html.twig', array(
            'filters' => $filters
        ));
    }

    /**
     * @Route("goal/done-modal", name="done_modal")
     * @return array
     */
    public function doneModalAction()
    {
        return $this->render('AppBundle:Goal:addSuccessStory.html.twig');
    }

    /**
     * @Route("goal/users", name="goal_users_modal")
     * @return array
     */
    public function goalUsersAction()
    {
        return $this->render('AppBundle:Goal:goalUsers.html.twig');
    }

    /**
     * @Route("user/common", name="common_modal")
     * @return array
     */
    public function userCommonAction()
    {
        return $this->render('AppBundle:Goal:common.html.twig');
    }
    
    /**
     * @Route("remove-profile/template", name="remove_profile_modal")
     * @return array
     */
    public function removeProfileAction()
    {
        return $this->render('AppBundle:Goal:removeProfile.html.twig');
    }

    /**
     * @Route("user/report", name="report_modal")
     * @return array
     */
    public function userReportAction()
    {
        return $this->render('AppBundle:Goal:report.html.twig');
    }

    /**
     * @Route("goal/create", name="add_goal")
     * @Template()
     * @Secure(roles="ROLE_USER")
     *
     * @param Request $request
     * @return array
     * @throws
     */
    public function addAction(Request $request)
    {
        $em          = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();

        $goalId      = $request->get('id');
        $cloneTrue   = $request->get('clone');
        $title   = $request->get('title', null);


        //If we clone or edit any goal
        if($goalId)
        {
            $goal = $em->getRepository("AppBundle:Goal")->findGoalWithAuthor($goalId);

            if(is_null($goal)){
                throw $this->createNotFoundException("Goal not found");
            }

            if (is_null($goal->getAuthor()) || $this->getUser()->getId() != $goal->getAuthor()->getId() || $goal->getPublish()){
                throw $this->createAccessDeniedException("It isn't your goal or it's already published");
            }

            $goal = $cloneTrue ? clone $goal : $goal;
        }
        else {
            $goal = new Goal();
        }

        if($title){
            $goal->setTitle($title);
        }

        $goal->setLanguage($currentUser->getLanguage());
        $goal->setAuthor($currentUser);


        $form  = $this->createForm(GoalType::class, $goal);

        if($request->isMethod("POST")){
            $form->handleRequest($request);

            if($form->isValid()){
                if ($videoLinks = $goal->getVideoLink()){
                    $videoLinks = array_values($videoLinks);
                    $videoLinks = array_filter($videoLinks);

                    $goal->setVideoLink($videoLinks);
                }

                $tags = $form->get('hashTags')->getData();
                $this->getAndAddTags($goal, $tags);

                // get images ids
                $images = $form->get('files')->getData();

                if($images){
                    $images     = json_decode($images);
                    $images     = array_unique($images);
                    $goalImages = $em->getRepository('AppBundle:GoalImage')->findByIDs($images);

                    if($goalImages){
                        foreach($goalImages as $goalImage){
                            $goal->addImage($goalImage);
                        }
                    }
                }

                if (!is_null($request->get("btn_publish"))) {

//                    $goal->setDescription(str_replace('#', '', $goal->getDescription()));

                    $em->persist($goal);
                    $em->flush();


                    if($goalId){
                        $em->getRepository('AppBundle:UserGoal')->updateUserGoals($goalId);
                    }
                    else {
                        $message = ($goal->getStatus() == Goal::PRIVATE_PRIVACY) ? 'goal.was_created.private' : 'goal.was_created.public';
                        $request->getSession()
                            ->getFlashBag()
                            ->set('success', $this->get('translator')->trans($message));
                    }

                    return new Response($goal->getId());
                }

                $em->persist($goal);
                $em->flush();
                if($goalId){
                    $em->getRepository('AppBundle:UserGoal')->updateUserGoals($goalId);
                }

                return  $this->redirectToRoute('view_goal', ['slug'=> $goal->getSlug()]);
            }
        }

        $slug = $request->get('slug', null);
        $isPrivate = ($slug == "drafts" || $slug == null) ? false : true;

        if($isPrivate){
            $request->getSession()
                ->getFlashBag()
                ->set('private','Edit my private idea from Web');
        }
        elseif ($goalId){
            $request->getSession()
                ->getFlashBag()
                ->set('draft','Edit my draft from Web');
        }


        return array('form' => $form->createView(), 'currentUser' => $currentUser, 'isPrivate' => $isPrivate, 'id' => $goalId);
    }

    /**
     * @Route("goal/my-ideas/{slug}", defaults={"slug" = null}, name="my_ideas")
     * @Template()
     * @Secure(roles="ROLE_USER")
     *
     * @return array
     * @param $slug
     * @param Request $request
     */
    public function myIdeasAction($slug = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if($slug == 'drafts'){
            // find all drafts goal
            $goals = $em->getRepository("AppBundle:Goal")->findMyDrafts($this->getUser());
        } else {
            // find all private goals
            $goals = $em->getRepository("AppBundle:Goal")->findMyPrivateGoals($this->getUser());
        }

        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $goals,
            $request->query->getInt('page', 1)/*page number*/,
            9/*limit per page*/
        );

        //This part is used for profile completion percent calculation
        if ($this->getUser()->getProfileCompletedPercent() != 100) {
            $em->getRepository("ApplicationUserBundle:User")->updatePercentStatuses($this->getUser());
        }

        $em->getRepository('ApplicationUserBundle:User')->setUserStats($this->getUser());

        return array(
            'goals'       => $pagination,
            'slug'        => $slug,
            'profileUser' => $this->getUser()
        );
    }

    /**
     * @Route("goal/view/{slug}", name="view_goal")
     * @Template()
     * @ParamConverter("goal", class="AppBundle:Goal",  options={
     *   "mapping": {"slug": "slug"},
     *   "repository_method" = "findBySlugWithRelations" })
     *
     * @param Goal $goal
     * @return array
     */
    public function viewAction(Goal $goal)
    {
        $this->denyAccessUnlessGranted('edit', $goal, $this->get('translator')->trans('goal.edit_access_denied'));

        return array('goal' => $goal);
    }

    /**
     * @Template("AppBundle:Blocks:goalInner.html.twig")
     *
     * @param $id
     * @param string $page
     * @return array
     */
    public function innerContentAction($id, $page = Goal::INNER)
    {
        $em = $this->getDoctrine()->getManager();
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();

        $goal = $em->getRepository('AppBundle:Goal')->findWithRelations($id);
        if (is_null($goal)){
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }

        $successStories = $goal->getSuccessStories()->toArray();
        usort($successStories, function($a, $b) {

            $aCount = $a->getSuccessStoryVoters()->count();
            $bCount = $b->getSuccessStoryVoters()->count();

            $currentDate = new \DateTime();
            $aInterval = date_diff($a->getUpdated(), $currentDate)->format('%R%a');
            $bInterval = date_diff($b->getUpdated(), $currentDate)->format('%R%a');

            if ($aInterval <= 7 || $bInterval <= 7){
                if ($aInterval == $bInterval) {
                    if ($aCount == $bCount) {
                        return 0;
                    }

                    return $aCount < $bCount ? 1 : -1;
                }

                return $a->getUpdated() < $b->getUpdated() ? 1 : -1;
            }

            if ($aCount == $bCount) {
                if ($a->getUpdated() == $b->getUpdated()){
                    return 0;
                }

                return $a->getUpdated() < $b->getUpdated() ? 1 : -1;
            }

            return $aCount < $bCount ? 1 : -1;
        });

        $goal->setSuccessStories($successStories);

        $this->denyAccessUnlessGranted('view', $goal, $this->get('translator')->trans('goal.view_access_denied'));

        $em->getRepository("AppBundle:Goal")->findGoalStateCount($goal);

        $doneByUsers   = $em->getRepository("AppBundle:Goal")->findGoalUsers($goal->getId(), UserGoal::COMPLETED, 0, 3);
        $listedByUsers = $em->getRepository("AppBundle:Goal")->findGoalUsers($goal, null, 0, 3 );

        // get aphorism by goal
        $aphorisms = $em->getRepository('AppBundle:Aphorism')->findOneRandom($goal);

        return array(
            'goal'          => $goal,
            'page'          => $page,
            'aphorisms'     => $aphorisms,
            'doneByUsers'   => $doneByUsers,
            'listedByUsers' => $listedByUsers
        );
    }

    /**
     * @Route("ideas/{category}", defaults={"category" = null}, name="goals_list")
     * @param Request $request
     * @param $category
     * @Template()
     * @return array
     */
    public function listAction(Request $request, $category = null)
    {
        $em = $this->getDoctrine()->getManager();
        
        if($category){
            $request->getSession()
                ->getFlashBag()
                ->set('category','Goal select category '.$category.' from Web')
            ;
        }

        $search = $request->get('search');
        $cachePrefix = (strpos($request->getUri(), self::STAGE_URL) === false) ? self::PROD_CACHE_PREFIX : self::STAGE_CACHE_PREFIX;

        $categories  = $em->getRepository('AppBundle:Category')->getAllCached($cachePrefix);

        $serializer = $this->get('serializer');
        $categoriesJson = $serializer->serialize($categories, 'json', SerializationContext::create()->setGroups(array('category')));

        return array('category' => $category, 'categories' => $categories, 'search' => $search, 'categoriesJson' => $categoriesJson);
    }


    /**
     * @param $object
     * @param $tags
     */
    private function getAndAddTags(&$object, $tags)
    {
        $em = $this->getDoctrine()->getManager();
        $env = $this->container->getParameter("kernel.environment");

        if($env == "test") {
            $tags = [];
        }

        if($tags){

            $tags = str_replace('#', '', $tags);
            $tags = json_decode($tags);

            $dbTags = $em->getRepository("AppBundle:Tag")->getTagTitles();

            $newTags = array_diff($tags, $dbTags);
            $newTags = array_unique($newTags);

            foreach($newTags as $tagString)
            {
                $tag = new Tag();
                $title = strtolower($tagString);
                $title = str_replace(',', '', $title);
                $title = str_replace(':', '', $title);
                $title = str_replace('.', '', $title);

                $tag->setTag($title);
                $object->addTag($tag);

                $em->persist($tag);
            }

            $existTags = array_diff($tags, $newTags);
            $oldTags = $em->getRepository("AppBundle:Tag")->findTagsByTitles($existTags);

            foreach($oldTags as $oldTag){
                if(!$object->getTags() || !$object->getTags()->contains($oldTag)){
                    $object->addTag($oldTag);
                    $em->persist($oldTag);
                }
            }
        }
    }

//    /**
//     * @Route("goal/{slug}", name="inner_goal_old")
//     * @Template()
//     * @ParamConverter("goal", class="AppBundle:Goal",  options={
//     *   "mapping": {"slug": "slug"},
//     *   "repository_method" = "findWithRelationsBySlug" })
//     *
//     * @param Goal $goal
//     * @return array
//     */
    public function oldShowAction(Goal $goal)
    {
        $this->denyAccessUnlessGranted('view', $goal, $this->get('translator')->trans('goal.view_access_denied'));

        return array('goal' => $goal);
    }

    /**
     * @Route("goal/{slug}", name="inner_goal")
     * @Template()
     * @ParamConverter("goal", class="AppBundle:Goal",  options={
     *   "mapping": {"slug": "slug"},
     *   "repository_method" = "findWithRelationsBySlug" })
     *
     * @param Goal $goal
     * @return array
     */
    public function ampShowAction(Goal $goal)
    {
        $this->denyAccessUnlessGranted('view', $goal, $this->get('translator')->trans('goal.view_access_denied'));

        $successStories = $goal->getSuccessStories()->toArray();
        usort($successStories, function($a, $b) {

            $aCount = $a->getSuccessStoryVoters()->count();
            $bCount = $b->getSuccessStoryVoters()->count();

            $currentDate = new \DateTime();
            $aInterval = date_diff($a->getUpdated(), $currentDate)->format('%R%a');
            $bInterval = date_diff($b->getUpdated(), $currentDate)->format('%R%a');

            if ($aInterval <= 7 || $bInterval <= 7){
                if ($aInterval == $bInterval) {
                    if ($aCount == $bCount) {
                        return 0;
                    }

                    return $aCount < $bCount ? 1 : -1;
                }

                return $a->getUpdated() < $b->getUpdated() ? 1 : -1;
            }

            if ($aCount == $bCount) {
                if ($a->getUpdated() == $b->getUpdated()){
                    return 0;
                }

                return $a->getUpdated() < $b->getUpdated() ? 1 : -1;
            }

            return $aCount < $bCount ? 1 : -1;
        });

        $goal->setSuccessStories($successStories);



        $em = $this->getDoctrine()->getmanager();
        $aphorisms = $em->getRepository('AppBundle:Aphorism')->findOneRandom($goal, true);

        $doneByUsers   = $em->getRepository("AppBundle:Goal")->findGoalUsers($goal->getId(), UserGoal::COMPLETED, 0, 3);
        $listedByUsers = $em->getRepository("AppBundle:Goal")->findGoalUsers($goal, null, 0, 3 );


        return array(
            'goal'          => $goal, 
            'aphorisms'     => $aphorisms,
            'doneByUsers'   => $doneByUsers,
            'listedByUsers' => $listedByUsers
        );
    }

    /**
     * @Route("clone/{slug}", name="clone_goal")
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("goal", class="AppBundle:Goal",  options={
     *   "mapping": {"slug": "slug"},
     *   "repository_method" = "findBySlugWithRelations" })
     *
     * @param Goal $goal
     * @return array
     * @deprecated must be removed
     */
    public function cloneAction(Goal $goal)
    {
        $em = $this->getDoctrine()->getManager();

        $object = clone $goal;

        $em->persist($object);
        $em->flush();

        return $this->redirectToRoute('add_goal', array('id' => $object->getId()));
    }
}
