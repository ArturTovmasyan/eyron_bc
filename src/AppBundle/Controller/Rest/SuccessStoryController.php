<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 6/27/16
 * Time: 6:20 PM
 */
namespace AppBundle\Controller\Rest;

use AppBundle\Entity\Goal;
use AppBundle\Entity\StoryImage;
use AppBundle\Entity\SuccessStory;
use Application\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Rest\RouteResource("Goal")
 * @Rest\Prefix("/api/v1.0")
 */
class SuccessStoryController extends FOSRestController
{
    /**
     * This function create success story.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function success story by goal",
     *  statusCodes={
     *         200="Returned when created",
     *         400="Return when content not correct",
     *         401="Return when user not found",
     *         404="Return when goal by goalId not found",
     *     },
     *  parameters={
     *      {"name"="story", "dataType"="text", "required"=true, "description"="story body"},
     *      {"name"="videoLink[0]", "dataType"="string", "required"=false, "description"="video link"},
     * }
     * )
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param $goal
     * @param Request $request
     * @return JsonResponse|Response
     * @deprecated
     * TODO will be changed after mobile changes
     */
    public function putSuccessstoryAction($goal, Request $request)
    {
        $this->container->get('bl.doctrine.listener')->disableIsMyGoalLoading();
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();
        $em = $this->container->get('doctrine')->getManager();
        $objGoal = $em->getRepository("AppBundle:Goal")->findWithRelations($goal);

        if(!$objGoal){
            return new JsonResponse('Goal not found', Response::HTTP_NOT_FOUND);
        }

        return $this->get('bl_story_service')->putSuccessStory($request, $objGoal, $this->getUser());
        
    }


    /**
     * TODO: Will be change all to this
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function create/edit success story",
     *  statusCodes={
     *         200="Returned when created",
     *         400="Return when content not correct",
     *         401="Return when user not found",
     *         404="Return when goal by goalId not found",
     *     },
     *  parameters={
     *      {"name"="story", "dataType"="text", "required"=true, "description"="story body"},
     *      {"name"="videoLink[0]", "dataType"="string", "required"=false, "description"="video link"},
     * }
     * )
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param $goal
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function putStoryAction($goal, Request $request)
    {
        $this->container->get('bl.doctrine.listener')->disableIsMyGoalLoading();
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();
        $em = $this->container->get('doctrine')->getManager();
        $objGoal = $em->getRepository("AppBundle:Goal")->findWithRelations($goal);

        if(!$objGoal){
            return new JsonResponse('Goal not found', Response::HTTP_NOT_FOUND);
        }

        return $this->get('bl_story_service')->putStory($request, $objGoal, $this->getUser());
        
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This action is used for upload image for a success story",
     *  statusCodes={
     *         200="Returned when image was uploaded",
     *         400="Returned when there are validation error",
     *         403="Returned when adding image to success story which author isn't current user",
     *         404="Returned when there aren't file, or success story not found",
     *  },
     *  parameters={
     *      {"name"="file", "dataType"="file", "required"=false, "description"="Goal's images"},
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="Success story id"},
     *      {"name"="userId", "dataType"="integer", "required"=true, "description"="User id"},
     *  }
     * )
     * )
     *
     * @Rest\Post("/success-story/{id}/add-images/{userId}", requirements={"id"="\d+", "userId"="\d+"}, name="app_rest_success_story_addimages", options={"method_prefix"=false})
     * @Rest\Post("/success-story/add-images")
     * @ParamConverter("user", class="ApplicationUserBundle:User", options={"mapping" = {"userId" = "id"}})
     * @ParamConverter("successStory", class="AppBundle:SuccessStory", options={"mapping" = {"id" = "id"}})
     * @param $successStory
     * @param $user
     * @param Request $request
     * @return JsonResponse
     * @Rest\View()
     */
    public function addSuccessStoryImagesAction(Request $request, SuccessStory $successStory = null, User $user = null)
    {
        $em = $this->getDoctrine()->getManager();

        if (is_null($user)){
            $user = $this->getUser();
            if (is_null($user) || !is_object($user)){
                return new Response("there aren't any user", Response::HTTP_FORBIDDEN);
            }
        }

        if (!is_null($successStory)){
            $this->denyAccessUnlessGranted('edit', $successStory, $this->get('translator')->trans('success_story.edit_access_denied'));
        }

        $file = $request->files->get('file');

        if (is_null($file)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $storyImage = new StoryImage();
        $storyImage->setFile($file);

        if (!is_null($successStory)){
            $storyImage->setStory($successStory);
            $successStory->addFile($storyImage);
        }

        $validator = $this->get('validator');
        $error = $validator->validate($storyImage, null, 'success_story');

        if (count($error) > 0) {
            return new JsonResponse($error[0]->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->get('bl_service')->uploadFile($storyImage);

        $em->persist($storyImage);
        $em->flush();

        return $storyImage->getId();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This action is used to get goal success story by current user",
     *  statusCodes={
     *         200="Returned when return success story",
     *         404="Returned when goal not found",
     *  },
     * )
     *
     * @Rest\Get("/story/{id}", requirements={"id"="\d+"}, name="app_rest_goal_getsuccessstory", options={"method_prefix"=false})
     * @ParamConverter("goal", class="AppBundle:Goal", options={"repository_method" = "findGoalWithAuthor"})
     * @Rest\View(serializerGroups={"tiny_goal", "goal_author", "tiny_user", "successStory", "successStory_storyImage", "goal_description", "storyImage", "image"})
     *
     * @param Goal $goal
     * @return array
     */
    public function getSuccessStoryAction(Goal $goal)
    {
//        $this->denyAccessUnlessGranted('view', $goal, $this->get('translator')->trans('goal.view_access_denied'));

        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository('AppBundle:SuccessStory')->findUserGoalStory($this->getUser()->getId(), $goal->getId());

        $em->getRepository("AppBundle:Goal")->findGoalStateCount($goal);

        $liipManager = $this->get('liip_imagine.cache.manager');
        if ($goal->getListPhotoDownloadLink()) {
            $goal->setCachedImage($liipManager->getBrowserPath($goal->getListPhotoDownloadLink(), 'goal_list_big'));
        }

        return [
            'goal' => $goal,
            'story' => count($story) ? $story[0] : null
        ];
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to remove goal image",
     *  statusCodes={
     *         200="Returned when image was removed",
     *         400="Returned when image hasn't goal or it's goal isn't current user's goal",
     *         404="Returned when goalImage not found",
     *  },
     * )
     *
     * @Security("has_role('ROLE_USER')")
     * @Rest\Get("/success-story/remove-images/{id}", requirements={"id"="\d+"}, name="get_goal_remove_story_image", options={"method_prefix"=false})
     *
     * @param StoryImage $storyImage
     * @return Response
     */
    public function getRemoveStoryImageAction(StoryImage $storyImage, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->denyAccessUnlessGranted('edit', $storyImage->getStory(), $this->get('translator')->trans('success_story.edit_access_denied'));

        $em->remove($storyImage);
        $em->flush();

        if ($request->get('_route') == 'get_goal_remove_story_image' && isset($_SERVER['HTTP_REFERER'])){
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        return new Response('', Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *  resource=true,
     *  section="SuccessStory",
     *  description="This function is used to add vote to story",
     *  statusCodes={
     *         200="Returned when user was added",
     *         400="Returned when user user want vote his story",
     *         401="Returned when user not found",
     *         404="Returned when success story not found",
     *  },
     * )
     *
     * @Security("has_role('ROLE_USER')")
     * @Rest\Get("/success-story/add-vote/{storyId}", requirements={"storyId"="\d+"}, name="add_goal_story_vote", options={"method_prefix"=false})
     *
     * @param $storyId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addStoryVoteAction($storyId)
    {
        $em = $this->getDoctrine()->getManager();
        $voting = $em->getRepository('AppBundle:SuccessStoryVoters')->findBy(array('successStory' => $storyId, 'user' => $this->getUser()));
        if ($voting) return new JsonResponse();
        return $this->get('bl_story_service')->voteStory($storyId, $this->getUser());
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="SuccessStory",
     *  description="This function is used to remove vote to story",
     *  statusCodes={
     *         200="Returned when user was removed",
     *         401="Returned when user not found",
     *         404="Returned when success story not found",
     *  },
     * )
     *
     * @Security("has_role('ROLE_USER')")
     * @Rest\Get("/success-story/remove-vote/{storyId}", requirements={"storyId"="\d+"}, name="remove_goal_story_vote", options={"method_prefix"=false})
     *
     * @param $storyId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeStoryVoteAction($storyId)
    {
        return $this->get('bl_story_service')->removeVoteStory($storyId, $this->getUser());
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="SuccessStory",
     *  description="This function is used to remove story",
     *  statusCodes={
     *         200="Returned when user was removed",
     *         401="Returned when user not found",
     *         404="Returned when success story not found",
     *  },
     * )
     *
     * @Security("has_role('ROLE_USER')")
     * @Rest\Delete("/success-story/remove/{storyId}", requirements={"storyId"="\d+"}, name="remove_goal_story", options={"method_prefix"=false})
     *
     * @param $storyId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeStoryAction($storyId)
    {
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository('AppBundle:SuccessStory')->findOneBy(array('id' => $storyId, 'user' => $this->getUser()));

        if(!$story){
            return new Response('Story not found', Response::HTTP_NOT_FOUND);
        }
        
        $em->remove($story);
        $em->flush();
        return new Response('', Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="SuccessStory",
     *  description="This function is used to get story voters",
     *  statusCodes={
     *         200="Returned when user was added",
     *         401="Returned when user not found",
     *         404="Returned when success story not found",
     *  },
     * )
     *
     * @Security("has_role('ROLE_USER')")
     * @Rest\Get("/success-story/voters/{storyId}/{first}/{count}", requirements={"storyId"="\d+", "first"="\d+", "count"="\d+"}, name="get_story_voters", options={"method_prefix"=false})
     * @Rest\View(serializerGroups={"user"})
     *
     * @param $storyId
     * @param $first
     * @param $count
     * @return array
     */
    public function getStoryVotersAction($storyId, $first, $count)
    {
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();
        $em = $this->getDoctrine()->getManager();
        $voters = $em->getRepository('AppBundle:SuccessStory')->findStoryVoters($storyId, $first, $count);

        $stats = $em->getRepository('ApplicationUserBundle:User')->findUsersStats(array_keys($voters));

        $commonCounts = $em->getRepository('AppBundle:Goal')->findCommonCounts($this->getUser()->getId(), array_keys($voters));

        foreach($voters as &$user) {
            $user->setCommonGoalsCount($commonCounts[$user->getId()]['commonGoals']);

            $user->setStats([
                "listedBy" => $stats[$user->getId()]['listedBy'] + $stats[$user->getId()]['doneBy'],
                "active"   => $stats[$user->getId()]['listedBy'],
                "doneBy"   => $stats[$user->getId()]['doneBy']
            ]); 
        }

        return array_values($voters);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="SuccessStory",
     *  description="This function is used to get inspire story",
     *  statusCodes={
     *         200="Returned when user was added"}
     * )
     *
     * @Rest\Get("/success-story/inspire", name="get_goal_inspire_story", options={"method_prefix"=false})
     *
     * @Rest\View(serializerGroups={"inspireStory"})
     *
     * @return JsonResponse
     */
    public function getInspireStoryAction()
    {
        //Create new Json Response
        $response = new JsonResponse();
        //get liipManager service
        $liipManager = $this->get('liip_imagine.cache.manager');

        //get entoty manager
        $em = $this->getDoctrine()->getManager();

        //get inspire story
        $stories = $em->getRepository("AppBundle:SuccessStory")->findInspireStories();

        $goalIds = [];
        foreach($stories as $story){
            $goalIds[$story->getGoal()->getId()] = 1;
        }

        //get goal stats count
        $stats = $em->getRepository("AppBundle:Goal")->findGoalStateCount($goalIds, true);
        
        foreach($stories as &$story){

            if ($story->getGoal() && $story->getGoal()->getListPhotoDownloadLink()) {
                $story->getGoal()->setCachedImage($liipManager->getBrowserPath($story->getGoal()->getListPhotoDownloadLink(), 'goal_list_horizontal'));
            }

            if($story->getUser() && $story->getUser()->getPhotoLink()){
                $story->getUser()->setCachedImage($liipManager->getBrowserPath($story->getUser()->getPhotoLink(), 'user_icon'));
            }
            

            if($story->getFiles()){
                foreach ($story->getFiles() as $file){
                    $file->setMobileImagePath($liipManager->getBrowserPath($file->getImagePath(), 'slide_max_size'));
                }
            }
            

            $story->getGoal()->setStats([
                'listedBy' => $stats[$story->getGoal()->getId()]['listedBy'],
                'doneBy'   => $stats[$story->getGoal()->getId()]['doneBy'],
            ]);
        }

        //serialization content by group
        $serializer = $this->get('serializer');
        $contentJson = $serializer->serialize($stories, 'json', SerializationContext::create()->setGroups('inspireStory'));
        $response->setContent($contentJson);

       return $response;
    }
}