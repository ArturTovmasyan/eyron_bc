<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 1/25/16
 * Time: 5:46 PM
 */
namespace AppBundle\Controller\Rest;

use AppBundle\Entity\Goal;
use AppBundle\Entity\GoalImage;
use AppBundle\Entity\UserGoal;
use AppBundle\Entity\UserPlace;
use AppBundle\Entity\Tag;
use Application\CommentBundle\Entity\Comment;
use Application\CommentBundle\Entity\Thread;
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
class GoalController extends FOSRestController
{
    const RandomGoalFriendCounts = 3;

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get autocomplete goals",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request"
     *  },
     * )
     *
     * @return mixed
     * @param Request $request
     *
     * @Rest\View()
     * @Rest\Get("/goals/get-autocomplete-items", name="app_rest_goal_autocomplete",))
     * @Security("has_role('ROLE_USER')")
     */
    public function gerAutocompleteAction(Request $request)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();
        $count = 10;

        $search = $request->get('text');

        // get near by goals
        $goals = $em->getRepository('AppBundle:Goal')->findGoalsByAutocomplete($search, $count);

        return array('items' => array_values($goals), 'more'=>false, 'status'=>'OK');
    }

    /**
     * @Rest\Get("/goals/nearby/{latitude}/{longitude}/{first}/{count}/{isCompleted}", requirements={"latitude" = "[-+]?(\d*[.])?\d+", "longitude" = "[-+]?(\d*[.])?\d+"}, name="get_goal_nearby", options={"method_prefix"=false})
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get near by goals",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *         400="Bad request",
     *  },
     *
     * )
     * @param Request $request
     * @param $longitude
     * @param $first
     * @param $count
     * @param $isCompleted
     * @param $latitude
     * @param Request $request
     * @return mixed
     * @Rest\View(serializerGroups={"tiny_goal"})
     */
    public function getNearbyAction(Request $request, $latitude, $longitude, $first, $count, $isCompleted)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();
        $user      = $this->getUser();

        // get near by goals
        $nearbyGoals = $em->getRepository('AppBundle:Goal')->findNearbyGoals($latitude, $longitude, $first, $count, $isCompleted, is_object($user) ? $user->getId() : null);

        $liipManager = $this->get('liip_imagine.cache.manager');

        $filters = [
            0 => 'goal_list_small',
            1 => 'goal_list_small',
            2 => 'goal_list_small',
            3 => 'goal_list_small',
            4 => 'goal_list_horizontal',
            5 => 'goal_list_big',
            6 => 'goal_list_vertical',
        ];

        //This part is used to calculate goal stats
        $goalIds   = [];
        $authorIds = [];
        foreach($nearbyGoals as $key => $goalArray){

            $goal = $goalArray[0];
            $dist = $goalArray['dist'];
            $goal->distance = $dist;
            $nearbyGoals[$key] = reset($nearbyGoals[$key]);

            $goalIds[$goal->getId()] = 1;
            if ($goal->getAuthor()) {
                $authorIds[] = $goal->getAuthor()->getId();
            }

            if ($goal->getListPhotoDownloadLink()) {
                try {
                    $goal->setCachedImage($liipManager->getBrowserPath($goal->getListPhotoDownloadLink(), $filters[$key]));
                } catch (\Exception $e) {
                    $goal->setCachedImage("");
                }
            }
            
        }

        $goalStats = $em->getRepository("AppBundle:Goal")->findGoalStateCount($goalIds, true);
        $authorstats = $em->getRepository("ApplicationUserBundle:User")->findUsersStats($authorIds);

        foreach($nearbyGoals as $goal){
            $goal->setStats([
                'listedBy' => $goalStats[$goal->getId()]['listedBy'],
                'doneBy'   => $goalStats[$goal->getId()]['doneBy'],
            ]);

            if ($goal->getAuthor()) {
                $stats = $authorstats[$goal->getAuthor()->getId()];
                $goal->getAuthor()->setStats([
                    "listedBy" => $stats['listedBy'] + $stats['doneBy'],
                    "active"   => $stats['listedBy'],
                    "doneBy"   => $stats['doneBy']
                ]);
            }
        }

        if($user){
            $em->getRepository('ApplicationUserBundle:User')->setUserStats($user);

        }
        
        return  $nearbyGoals;
    }

    /**
     * @Rest\Get("/goals/{userId}/owned/{first}/{count}", defaults={"first"=null, "count"=null}, requirements={"first"="\d+", "count"="\d+"}, name="get_goal_owned", options={"method_prefix"=false})
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get owned goals",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     *
     * )
     *
     * @param int $userId
     * @param int $first
     * @param int $count
     * @param Request $request
     * @return mixed
     * @Rest\View(serializerGroups={"userGoal", "userGoal_goal", "tiny_goal", "goal_author", "tiny_user"})
     */
    public function getOwnedAction(Request $request, $userId, $first = null, $count = null)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        $isMy = $this->getUser()->getId() == $userId;
        $user      = $userId ? $em->getRepository('ApplicationUserBundle:User')->find($userId) : $this->getUser();
        // check user
        if(!$user) {
            return new JsonResponse(array('error' => 'User not found'), Response::HTTP_NOT_FOUND);
        }

        $goalService = $this->container->get('app.goal');
        return  $goalService->getOwnedData($request, $user, $isMy, $first, $count);
    }


    /**
     * @Rest\Get("/goals/{userId}/common/{first}/{count}", defaults={"first"=null, "count"=null}, requirements={"first"="\d+", "count"="\d+"}, name="get_goal_common", options={"method_prefix"=false})
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get common goals",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     *
     * )
     *
     * @param int $userId
     * @param int $first
     * @param int $count
     * @param Request $request
     * @return mixed
     * @Rest\View(serializerGroups={"tiny_goal"})
     */
    public function getCommonAction(Request $request, $userId, $first = null, $count = null)
    {
        $em = $this->getDoctrine()->getManager();
        $commonGoals = $em->getRepository('AppBundle:Goal')->findCommonGoals($this->getUser()->getId(), $userId, $first, $count);
        $em->getRepository("AppBundle:Goal")->findGoalStateCount($commonGoals);

        $liipManager = $this->get('liip_imagine.cache.manager');

        foreach($commonGoals as $goal) {
            if ($goal->getListPhotoDownloadLink()) {
                try {
                    $goal->setCachedImage($liipManager->getBrowserPath($goal->getListPhotoDownloadLink(), 'goal_list_horizontal'));
                } catch (\Exception $e) {
                    $goal->setCachedImage("");
                }
            }
        }

        return  ['goals' => array_values($commonGoals) ];
    }

    /**
     * @Rest\Get("/goals/{first}/{count}", requirements={"first"="\d+", "count"="\d+"}, name="app_rest_goal_getall", options={"method_prefix"=false})
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get goal",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     *  parameters={
     *      {"name"="category", "dataType"="string", "required"=false, "description"="Goals category slug"},
     *      {"name"="search", "dataType"="string", "required"=false, "description"="search data"}
     *  }
     *
     *
     * )
     *
     * @param int $first
     * @param int $count
     * @param Request $request
     * @return mixed
     * @Rest\View(serializerGroups={"tiny_goal"})
     */
    public function getAllAction($first, $count, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $request->get('category');
        $search = $request->get('search');

        $goals = $em->getRepository("AppBundle:Goal")->findAllByCategory($category, $search, $first, $count);
        $em->getRepository("AppBundle:Goal")->findGoalStateCount($goals);

        $goals = array_values($goals);

        $filters = [
            0 => 'goal_list_small',
            1 => 'goal_list_small',
            2 => 'goal_list_small',
            3 => 'goal_list_small',
            4 => 'goal_list_horizontal',
            5 => 'goal_list_big',
            6 => 'goal_list_vertical',
        ];

        $liipManager = $this->get('liip_imagine.cache.manager');

        if ($count == 7 || $count == 3){
            for($i = 0; $i < 7; $i++){
                if (isset($goals[$i]) && $goals[$i]->getListPhotoDownloadLink()) {
                    $goals[$i]->setCachedImage($liipManager->getBrowserPath($goals[$i]->getListPhotoDownloadLink(), $filters[$i]));
                }
            }
        }elseif ($count == 9){
            foreach($goals as $goal){
                if (isset($goal) && $goal->getListPhotoDownloadLink()) {
                    $goal->setCachedImage($liipManager->getBrowserPath($goal->getListPhotoDownloadLink(), $filters[0]));
                }
            }
        }

        return  $goals;
    }

    /**
     * @Rest\Get("/goals/discover", name="app_rest_goal_getdiscovergoal ", options={"method_prefix"=false})
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get discover goal",
     *  statusCodes={
     *         200="Ok",
     *  }
     * )
     *
     * @param Request $request
     * @return mixed
     * @Rest\View(serializerGroups={"tiny_goal"})
     */
    public function getDiscoverGoalAction(Request $request)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();

        //get popular goals
        $goals = $em->getRepository("AppBundle:Goal")->findPopular(7);

        $count = count($goals);

        //set stats count in goal
        $em->getRepository("AppBundle:Goal")->findGoalStateCount($goals);
        $goals = array_values($goals);

        //generate filters
        $filters = [
            0 => 'goal_list_small',
            1 => 'goal_list_small',
            2 => 'goal_list_vertical',
            3 => 'goal_list_small',
            4 => 'goal_list_small',
            5 => 'goal_list_horizontal',
            6 => 'goal_list_small',
        ];

        //get liipManager service
        $liipManager = $this->get('liip_imagine.cache.manager');

        //check if goals count is 7
        if ($count <= 7){

            //set calculate value
            $i = 0;

            foreach ($goals as $goal) {

                if (isset($goal) && $goal->getListPhotoDownloadLink()) {
                    $goal->setCachedImage($liipManager->getBrowserPath($goal->getListPhotoDownloadLink(), $filters[$i]));
                    $i++;
                }
            }
        }

        return $goals;
    }

    /**
     * @Rest\Get("/top-ideas/{count}", requirements={"count"="\d+"}, name="app_rest_top_ideas", options={"method_prefix"=false})
     * @Rest\Get("/goals/{count}/suggest", requirements={"count"="\d+"})
     * @ApiDoc(
     *  resource=true,
     *  section="Activity",
     *  description="This function is used to get top ideas",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"tiny_goal"})
     * @Security("has_role('ROLE_USER')")
     *
     * @param $count
     * @return array
     */
    public function getTopIdeasAction($count)
    {
        $em = $this->getDoctrine()->getManager();

        $topIdeas = $em->getRepository("AppBundle:Goal")->findPopular($count, $this->getUser());
        $em->getRepository("AppBundle:Goal")->findGoalStateCount($topIdeas);

        $liipManager = $this->get('liip_imagine.cache.manager');
        foreach($topIdeas as $topIdea){

            if($topIdea->getListPhotoDownloadLink()){
                $topIdea->setCachedImage($liipManager->getBrowserPath($topIdea->getListPhotoDownloadLink(), 'goal_list_small'));
            }

        }

        return array_values($topIdeas);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Activity",
     *  description="This function is used to get featured ideas",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"tiny_goal"})
     * @Security("has_role('ROLE_USER')")
     *
     * @return array
     */
    public function getFeaturedAction()
    {
        $em = $this->getDoctrine()->getManager();

        $topIdeas = $em->getRepository("AppBundle:Goal")->findFeatured($this->getUser());
        $em->getRepository("AppBundle:Goal")->findGoalStateCount($topIdeas);

        $liipManager = $this->get('liip_imagine.cache.manager');
        foreach($topIdeas as $topIdea){

            if($topIdea->getListPhotoDownloadLink()){
                $topIdea->setCachedImage($liipManager->getBrowserPath($topIdea->getListPhotoDownloadLink(), 'goal_list_small'));
            }
        }

        return array_values($topIdeas);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get goal by id",
     *  statusCodes={
     *         200="Returned when goal was found",
     *         404="Returned when goal was not found",
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"goal", "goal_image", "image", "goal_author", "tiny_user",
     *                              "goal_successStory", "successStory", "successStory_user", "successStory_storyImage",
     *                              "successStory_user", "tiny_user", "storyImage", "comment", "comment_author", "comment_children"})
     *
     * @param $id
     * @return Goal|null|object|Response|array
     */
    public function getAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $goal = $em->getRepository('AppBundle:Goal')->findWithRelations($id);
        $this->denyAccessUnlessGranted('view', $goal, $this->get('translator')->trans('goal.view_access_denied'));

        if (!$goal){
            return new Response('Goal not found', Response::HTTP_NOT_FOUND);
        }

        $em->getRepository("AppBundle:Goal")->findGoalStateCount($goal);
        $goalComments = $em->getRepository('ApplicationCommentBundle:Comment')->findThreadComments('goal_' . $goal->getSlug());

        if ((!$goal->getLat() || !$goal->getLng()) && $this->getUser()){
            $userGoals = $this->getUser()->getUserGoal();

            if($userGoals->count() > 0) {
                $userGoalsArray = $userGoals->toArray();
                if (array_key_exists($id, $userGoalsArray)) {
                    $userGoal = $userGoalsArray[$id];
                    $goal->setLat($userGoal->getLat());
                    $goal->setLng($userGoal->getLng());
                }
            }
        }

        return [
            'goal'     => $goal,
            'comments' => $goalComments
        ];
    }

    /**
     * @Rest\Get("/goal/by-slug/{slug}", name="get_goal_goal_by_slug", options={"method_prefix"=false})
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get goal by slug",
     *  statusCodes={
     *         200="Returned when goal was found",
     *         404="Returned when goal was not found",
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"goal", "tiny_goal", "aphorism", "goal_image", "image", "goal_author", "tiny_user", "userGoal",
     *                              "goal_successStory", "successStory", "successStory_user", "successStory_storyImage",
     *                              "successStory_user", "tiny_user", "storyImage", "comment", "comment_author", "comment_children"})
     * @param $slug string
     * @return JsonResponse|array
     */
    public function getGoalBySlugAction($slug)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get goal by slug
        $goal = $em->getRepository('AppBundle:Goal')->findBySlugWithTinyRelations($slug);

        $em->getRepository("AppBundle:Goal")->findGoalStateCount($goal);

        //check if goal not exist
        if(!$goal) {
            return new JsonResponse("Goal by $slug not found", Response::HTTP_NOT_FOUND);
        }

        $liipManager = $this->get('liip_imagine.cache.manager');
        
        if($goal->getImagePath()) {
            $goal->setCachedImage($liipManager->getBrowserPath($goal->getImagePath(), 'goal_bg'));
        }

        if($goal->getImages()){
            foreach ($goal->getImages() as $image){
                $image->setMobileImagePath($liipManager->getBrowserPath($image->getImagePath(), 'goal_bg'));
            }
        }
//todo optimized
        if($goal->getSuccessStories()){
            foreach ($goal->getSuccessStories() as $story){
                if($story->getUser()->getImagePath()){
                    $story->getUser()->setCachedImage($liipManager->getBrowserPath($story->getUser()->getImagePath(), 'user_icon'));
                }

                if($story->getFiles()){
                    foreach ($story->getFiles() as $file){
                        $file->setMobileImagePath($liipManager->getBrowserPath($file->getImagePath(), 'goal_list_small'));
                    }
                }
            }
        }

        // get aphorism by goal
        $aphorisms = $em->getRepository('AppBundle:Aphorism')->findOneRandom($goal);

        $doneByUsers   = $em->getRepository("AppBundle:Goal")->findGoalUsers($goal->getId(), UserGoal::COMPLETED, 0, 3);
        $listedByUsers = $em->getRepository("AppBundle:Goal")->findGoalUsers($goal, null, 0, 3 );
        //check access
        $this->denyAccessUnlessGranted('view', $goal, $this->get('translator')->trans('goal.view_access_denied'));

        return array(
            'goal' => $goal,
            'aphorisms' => $aphorisms,
            'doneByUsers' => $doneByUsers,
            'listedByUsers' =>$listedByUsers,
        );
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get all categories",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"category"})
     */
    public function getCategoriesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories  = $em->getRepository('AppBundle:Category')->findAll();

        return $categories;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to create a goal",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *         404="Returned when goal was not found",
     *  },
     *  parameters={
     *      {"name"="is_public", "dataType"="boolean", "required"=true, "description"="Goal's status"},
     *      {"name"="title", "dataType"="string", "required"=true, "description"="Goal's title"},
     *      {"name"="description", "dataType"="string", "required"=false, "description"="Goal's description"},
     *      {"name"="video_links[0]", "dataType"="string", "required"=false, "description"="Goal's video links"},
     *      {"name"="language", "dataType"="string", "required"=false, "description"="Goal's language"}
     *  }
     * )
     *
     * @param Request $request
     * @param $id
     * @return mixed
     * @Rest\Put("/goals/create/{id}", defaults={"id"=null}, requirements={"id"="\d+"}, name="app_rest_goal_put", options={"method_prefix"=false})
     * @Rest\Post("/goals/create/{id}", defaults={"id"=null}, requirements={"id"="\d+"}, name="app_rest_goal_post", options={"method_prefix"=false})
     * @Rest\View()
     * @Security("has_role('ROLE_USER')")
     */
    public function putAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();

        if($request->getContentType() == 'application/json' || $request->getContentType() == 'json'){
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        $data = $request->request->all();

        if ($id){
            $goal = $em->getRepository('AppBundle:Goal')->find($id);
            if (!$goal){
                return new Response("Goal wasn't found", Response::HTTP_NOT_FOUND);
            }

            $this->denyAccessUnlessGranted('edit', $goal, $this->get('translator')->trans('goal.edit_access_denied'));
            $em->getRepository('AppBundle:UserGoal')->updateUserGoals($id);
        }
        else {
            $goal = new Goal();
        }

        $goal->setStatus(array_key_exists('is_public', $data) && $data['is_public']  ? Goal::PUBLIC_PRIVACY : Goal::PRIVATE_PRIVACY);
        $goal->setTitle(array_key_exists('title', $data)                             ? $data['title']       : null);
        $goal->setDescription(array_key_exists('description', $data)                 ? $data['description'] : null);
        $goal->setVideoLink(array_key_exists('video_links', $data)                   ? $data['video_links'] : null);
        $goal->setLanguage(array_key_exists('language', $data)                       ? $data['language']    : "en");
        $goal->setReadinessStatus(Goal::DRAFT);
        $goal->setAuthor($this->getUser());

        if(array_key_exists('files', $data) && $data['files']){
            $images = $data['files'];
            $imageIds = array_unique($images);
            if($id){
                foreach($goal->getImages() as $file){
                    if (!in_array($file->getId(), $imageIds)){
                        $em->remove($file);
                        $goal->removeImage($file);
                    }
                }
            } else {
                
                $goalImages = $em->getRepository('AppBundle:GoalImage')->findByIDs($imageIds);

                if(count($goalImages) != 0){
                    foreach($goalImages as $goalImage){
                        $goal->addImage($goalImage);
                    }
                }
            }
            
        }
        
        if(array_key_exists('tags', $data) && $data['tags']){
            $tags = $data['tags'];

            $tags = implode(" ", $tags);
            $tags = str_replace('#', '', $tags);
            $tags = explode(" ", $tags);
            
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
                $goal->addTag($tag);

//                $em->persist($tag);
            }

            $existTags = array_diff($tags, $newTags);
            $oldTags = $em->getRepository("AppBundle:Tag")->findTagsByTitles($existTags);

            foreach($oldTags as $oldTag){
                if(!$goal->getTags() || !$goal->getTags()->contains($oldTag)){
                    $goal->addTag($oldTag);
                    $em->persist($oldTag);
                }
            }
        }

        $validator = $this->get('validator');
        $error = $validator->validate($goal, null, array('goal'));

        if(count($error) > 0){
            return new JsonResponse($error[0]->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $em->persist($goal);
        $em->flush();

        return array(
            'id'   => $goal->getId(),
            'slug' => $goal->getSlug());
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This action is used for upload image for a goal",
     *  statusCodes={
     *         200="Returned when image was uploaded",
     *         400="Returned when there are validation error",
     *         403="Returned when adding image to goal which author isn't current user",
     *         404="Returned when there aren't file, or goal not found",
     *  },
     *  parameters={
     *      {"name"="file", "dataType"="file", "required"=false, "description"="Goal's images"}
     *  }
     * )
     *
     * @Rest\Post("/goals/add-images/{id}/{userId}", defaults={"id"=null, "userId"=null}, requirements={"id"="\d+", "userId"="\d+"}, name="app_rest_goal_addimages", options={"method_prefix"=false})
     * @ParamConverter("user", class="ApplicationUserBundle:User", options={"id" = "userId"})
     * @param $goal
     * @param $user
     * @param Request $request
     * @return JsonResponse
     * @Rest\View()
     */
    public function addImagesAction(Request $request, Goal $goal = null, User $user = null)
    {
        //TODO this rest non secured will be changed after tokens strategy
        $em = $this->getDoctrine()->getManager();

        if (is_null($user)){
            $user = $this->getUser();
            if (is_null($user) || !is_object($user)){
                return new Response("there aren't any user", Response::HTTP_FORBIDDEN);
            }
        }

        if (!is_null($goal)){
            $this->denyAccessUnlessGranted('edit', $goal, $this->get('translator')->trans('goal.edit_access_denied'));
        }

        $file = $request->files->get('file');

        if(is_null($file)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $goalImage = new GoalImage();
        $goalImage->setFile($file);

        if (!is_null($goal)){
            $goalImage->setGoal($goal);
            $goal->addImage($goalImage);
        }

        $validator = $this->get('validator');
        $error = $validator->validate($goalImage, null, array('goal'));

        if(count($error) > 0){
            return new JsonResponse($error[0]->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->get('bl_service')->uploadFile($goalImage);

        $em->persist($goalImage);
        $em->flush();

        return $goalImage->getId();
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
     * @Rest\Post("/goals/remove-images/{id}", requirements={"id"="\d+"}, name="app_rest_goal_removeimage", options={"method_prefix"=false})
     * @Rest\Get("/goals/remove-images/{id}", requirements={"id"="\d+"}, name="app_get_rest_goal_removeimage", options={"method_prefix"=false})
     * @Security("has_role('ROLE_USER')")
     *
     * @param GoalImage $goalImage
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeImageAction(GoalImage $goalImage, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($goal = $goalImage->getGoal()))
        {
            $this->denyAccessUnlessGranted('edit', $goal, $this->get('translator')->trans('goal.edit_access_denied'));

            $goal->removeImage($goalImage);
            $goalImages = $goal->getImages();
            if ($goalImage->getList() && $goalImages->first()){
                $goalImages->first()->setList(true);
            }
            if ($goalImage->getCover() && $goalImages->first()){
                $goalImages->first()->setCover(true);
            }
        }

        $em->remove($goalImage);
        $em->flush();

        if ($request->get('_route') == 'app_get_rest_goal_removeimage' && isset($_SERVER['HTTP_REFERER'])){
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        return new Response('', Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get user draft goals",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"goal_draft"})
     *
     * @Rest\Get("/goals/drafts/{first}/{count}", requirements={"first"="\d+", "count"="\d+"}, name="app_rest_goal_getdrafts", options={"method_prefix"=false})
     * @Security("has_role('ROLE_USER')")
     *
     * @param $first
     * @param $count
     * @return array
     */
    public function getDraftsAction($first, $count)
    {
        $em = $this->getDoctrine()->getManager();
        $draftGoals = $em->getRepository("AppBundle:Goal")->findMyDrafts($this->getUser(), $first, $count);

        $liipManager = $this->get('liip_imagine.cache.manager');

        foreach($draftGoals as $draftGoal){
            if ($draftGoal->getListPhotoDownloadLink()) {
                $draftGoal->setCachedImage($liipManager->getBrowserPath($draftGoal->getListPhotoDownloadLink(), 'goal_list_small'));
            }
        }
        return $draftGoals;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get user private goals",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"goal_draft"})
     *
     * @Rest\Get("/goals/private/{first}/{count}", requirements={"first"="\d+", "count"="\d+"}, name="app_rest_goal_getprivategoals", options={"method_prefix"=false})
     * @Security("has_role('ROLE_USER')")
     *
     * @param $first
     * @param $count
     * @return array
     */
    public function getPrivatesAction($first, $count)
    {
        $em = $this->getDoctrine()->getManager();
        $privateGoals = $em->getRepository("AppBundle:Goal")->findMyPrivateGoals($this->getUser(), $first, $count);

        $liipManager = $this->get('liip_imagine.cache.manager');

        foreach($privateGoals as $privateGoal){
            if ($privateGoal->getListPhotoDownloadLink()) {
                $privateGoal->setCachedImage($liipManager->getBrowserPath($privateGoal->getListPhotoDownloadLink(), 'goal_list_small'));
            }
        }
        return $privateGoals;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to remove drafts",
     *  statusCodes={
     *         200="Returned when draft was removed",
     *         404="Not found",
     *         403="Returned when removed drafts to goal which author isn't current user",
     *  },
     * )
     * @Rest\View()
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("goal", class="AppBundle:Goal")
     * @param $request
     * @param $goal
     * @param $slug
     * @return array
     *
     * @Rest\Delete("/goals/{goal}/drafts", requirements={"goal"="\d+"}, name="delete_goal_drafts", options={"method_prefix"=false})
     * @Rest\Get("/goal/remove-ideas/{goal}/{slug}", requirements={"goal"="\d+"}, defaults={"slug" = null}, name="remove_my_ideas", options={"method_prefix"=false})
     */
    public function deleteDraftsAction(Request $request, Goal $goal, $slug = null)
    {
        $em = $this->getDoctrine()->getManager();
        $userGoal = $em->getRepository('AppBundle:UserGoal')->findByUserAndGoal($this->getUser()->getId(), $goal->getId());

        if(!is_null($userGoal)){
            $em->remove($userGoal);
        }

        $this->denyAccessUnlessGranted('delete', $goal, $this->get('translator')->trans('goal.delete_access_denied'));
        $em->remove($goal);
        $em->flush();

        if ($request->get('_route') == "remove_my_ideas") {
            if ($slug == "drafts") {
                $request->getSession()
                    ->getFlashBag()
                    ->set('draft', 'Delete my draft from Web');
            } else {
                $request->getSession()
                    ->getFlashBag()
                    ->set('private', 'Delete my private idea from Web');
            }

            return $this->redirectToRoute("my_ideas", array('slug' => $slug));
        }

        return new Response('', Response::HTTP_OK);
    }


    /**
     * @Rest\Get("/goals/{first}/friends/{count}", defaults={"type"="all"}, requirements={"first"="\d+", "count"="\d+"}, name="get_goal_friends", options={"method_prefix"=false})
     * @Rest\Get("/user-list/{first}/{count}/{goalId}/{slug}", defaults={"goalId"=null, "slug"=null}, requirements={"first"="\d+", "count"="\d+", "goalId"="\d+", "slug"="1|2|3"}, name="get_goal_user_list")
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get user goal friends",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     * )
     * @Rest\View(serializerGroups={"user"})
     *
     * @param Request $request
     * @param $first
     * @param $count
     * @param null $goalId
     * @param null $slug
     * @return array
     */
    public function getFriendsAction(Request $request, $first, $count, $goalId = null, $slug = null)
    {
        if ($request->get('route_') == 'get_goal_friends' && !is_object($this->getUser())){
            throw new HttpException(401);
        }

        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();
        $search = $request->get('search', null);
        $em = $this->getDoctrine()->getManager();

        if (!is_null($goalId)){
            if($slug == 3){
                $users = $em->getRepository('ApplicationUserBundle:User')
                    ->votingUsers($goalId, $first, $count);
            }else{
                $users = $em->getRepository('AppBundle:Goal')
                    ->findGoalUsers($goalId, $slug == 1 ? UserGoal::ACTIVE : UserGoal::COMPLETED, $first, $count, $search);
            }
            
        }
        else {
            $type = $request->get('type', 'all');
            if (!in_array($type, ["all","recently","match","active", "follow"])){
                throw new HttpException(Response::HTTP_BAD_REQUEST);
            }

            $users = $em->getRepository('AppBundle:Goal')->findGoalFriends($this->getUser()->getId(), $type, $search, $first, $count);
        }

        $userIds = array_keys($users);
        $stats = $em->getRepository('ApplicationUserBundle:User')->findUsersStats($userIds);

        if (is_object($this->getUser())) {
            $commonCounts = $em->getRepository('AppBundle:Goal')->findCommonCounts($this->getUser()->getId(), $userIds);
        }

        foreach($users as &$user) {
            if (is_object($this->getUser())) {
                $user->setCommonGoalsCount($commonCounts[$user->getId()]['commonGoals']);
            }

            $user->setStats([
                "listedBy" => $stats[$user->getId()]['listedBy'] + $stats[$user->getId()]['doneBy'],
                "active"   => $stats[$user->getId()]['listedBy'],
                "doneBy"   => $stats[$user->getId()]['doneBy']
            ]);
        }

        return array_values($users);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Activity",
     *  description="This function is used to get goal friends",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"user", "tiny_user"})
     * @Security("has_role('ROLE_USER')")
     *
     * @return array
     */
    public function getRandomFriendsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $allCount = 0;
        $goalFriends = $em->getRepository("AppBundle:Goal")->findRandomGoalFriends($this->getUser()->getId(), self::RandomGoalFriendCounts, $allCount);

        $liipManager = $this->get('liip_imagine.cache.manager');

        foreach($goalFriends as $goalFriend){

            if($goalFriend->getImagePath()){
                $goalFriend->setCachedImage($liipManager->getBrowserPath($goalFriend->getImagePath(), 'user_icon'));
            }
            else {
                $name = $goalFriend->getFirstName()[0] . $goalFriend->getLastName()[0];
                $goalFriend->setCachedImage($name);
            }
        }

        return [
            '1'      => $goalFriends,
            'length' => $allCount
        ];
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get goal image path",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"image_link"})
     *
     * @Rest\Get("/goals/image/{id}", requirements={"id"="\d+"}, name="app_rest_goal_image", options={"method_prefix"=false})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Goal $goal
     * @return array
     */
    public function getImageAction(Goal $goal)
    {
        return $goal;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get goal image path",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     * )
     *
     * @Rest\View()
     *
     * @Rest\Get("/goals/title/{id}", requirements={"id"="\d+"}, name="app_rest_goal_title",)
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Goal $goal
     * @return array
     */
    public function getTitleAction(Goal $goal)
    {
        return array("title" => $goal->getTitle());
    }

//    /**
//     * @ApiDoc(
//     *  resource=true,
//     *  section="Goal",
//     *  description="This function is used to get goals in place",
//     *  statusCodes={
//     *         204="No content",
//     *         400="Bad request"
//     *  },
//     * )
//     *
//     * @return mixed
//     * @param $latitude float
//     * @param $longitude float
//     *
//     * @Rest\View(serializerGroups={"goal"})
//     * @Rest\Get("/goals/places/{latitude}/{longitude}", requirements={"latitude" = "[-+]?(\d*[.])?\d+", "longitude" = "[-+]?(\d*[.])?\d+"}))
//     * @Security("has_role('ROLE_USER')")
//     */
//    public function getGoalsInPlaceAction($latitude, $longitude)
//    {
//        //check if coordinate exist
//        if ($latitude && $longitude) {
//
//            //get place service
//            $placeService = $this->get('app.place');
//
//            //get current user
//            $user = $this->getUser();
//
//            //get all goals by place
//            $allGoals = $placeService->getAllGoalsByPlace($latitude, $longitude, $user);
//
//            //check if goal not exists
//            if (!$allGoals) {
//                return new Response('', Response::HTTP_NO_CONTENT);
//            }
//
//            return $allGoals;
//        }
//
//        return new Response('Missing coordinate data', Response::HTTP_BAD_REQUEST);
//    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to put user position",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request"
     *  },
     * )
     *
     * @return mixed
     * @param $latitude float
     * @param $longitude float
     *
     * @Rest\View()
     * @Rest\PUT("/goals/user-position/{latitude}/{longitude}", requirements={"latitude" = "[-+]?(\d*[.])?\d+", "longitude" = "[-+]?(\d*[.])?\d+"}))
     * @Security("has_role('ROLE_USER')")
     */
    public function putUserPositionAction($latitude, $longitude)
    {
        //check if coordinate exist
        if ($latitude && $longitude) {

            //get place service
            $placeService = $this->get('app.place');

            //get current user
            $user = $this->getUser();

            //get all goals by place
            $placeService->createPlace($latitude, $longitude, $user);

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return new Response('Missing coordinate data', Response::HTTP_BAD_REQUEST);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Goal",
     *  description="This function is used to get goals in place",
     *  statusCodes={
     *         200="OK",
     *         400="Bad request"
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"goal"})
     * @Rest\GET("/goals/place/un-confirm")
     * @Security("has_role('ROLE_USER')")
     */
    public function getUserUnConfirmAction()
    {
        //get current user
        $user = $this->getUser();

        $em = $this->get("doctrine")->getManager();

        $goals = $em->getRepository("AppBundle:Goal")->findUserUnConfirmInPlace($user);

        //set confirm default value
        $confirm = false;

        //check if goals exist
        if ($goals) {

            //confirm done goal
            foreach ($goals as $goal)
            {

                //get user goal by user id
                $userGoals = $goal->getUserGoal();

                //get current user userGoal
                $relatedUserGoal = $userGoals->filter(function ($item) use ($user) {
                    return $item->getUser() == $user ? true : false;
                });

                //check if user have user goal
                if ($relatedUserGoal->count() > 0) {

                    //get related user goal
                    $userGoal = $relatedUserGoal->first();

                    //check if user goal is not completed
                    if (!$userGoal->getConfirmed()) {

                        //confirmed done goal for user
                        $userGoal->setConfirmed(true);

                        //set confirm value
                        $confirm = true;

                        $em->persist($userGoal);
                    }
                }
            }

            //check if user has confirmed goal
            if ($confirm) {
                $em->flush();
                $em->clear();
            }
        }

        return $goals;
    }

//    /**
//     * @ApiDoc(
//     *  resource=true,
//     *  section="Goal",
//     *  description="This function is used to confirm goals",
//     *  statusCodes={
//     *         200="Ok",
//     *         400="Bad request",
//     *         404="Not found"
//     *  },
//     *  parameters={
//     *      {"name"="goal", "dataType"="array", "required"=true, "description"="Goal ids with userGoal visible status"},
//     *      {"name"="latitude", "dataType"="float", "required"=true, "description"="latitude"},
//     *      {"name"="longitude", "dataType"="float", "required"=true, "description"="longitude"}
//     *  }
//     * )
//     *
//     * @return array
//     * @param $request
//     *
//     * @Rest\View()
//     * @Rest\Post("/goals/confirm")
//     * @Security("has_role('ROLE_USER')")
//     */
//    public function postConfirmGoalsAction(Request $request)
//    {
//        //get entity manager
//        $em = $this->getDoctrine()->getManager();
//
//        //get goal data in request
//        $goalData = $request->get('goal');
//
//        //get latitude
//        $latitude = $request->get('latitude');
//
//        //get longitude
//        $longitude = $request->get('longitude');
//
//        //check if goal ids not send
//        if(!$goalData || !$latitude || !$longitude) {
//           return new Response('Request data is empty', Response::HTTP_BAD_REQUEST);
//        }
//
//        //get goal ids
//        $goalIds = $goalData ? array_keys($goalData) : null;
//
//        //get current user
//        $user = $this->getUser();
//
//        //get all goals by ids
//        $goals = $em->getRepository('AppBundle:Goal')->findAllByIds($goalIds);
//
//        //check if goals exist
//        if ($goals) {
//
//            //confirm done goal
//            foreach ($goals as $goal)
//            {
//                //set confirm default value
//                $confirm = false;
//
//                //get user goal by user id
//                $userGoals = $goal->getUserGoal();
//
//                //get current user userGoal
//                $relatedUserGoal = $userGoals->filter(function ($item) use ($user) {
//                    return $item->getUser() == $user ? true : false;
//                });
//
//                //check if user have user goal
//                if ($relatedUserGoal->count() > 0) {
//
//                    //get related user goal
//                    $userGoal = $relatedUserGoal->first();
//
//                    //check if user goal not confirmed
//                    if (!$userGoal->getConfirmed()) {
//
//                        //confirmed done goal for user
//                        $userGoal->setConfirmed(true);
//
//                        //check if user goal is not completed
//                        if ($userGoal->getStatus() !== UserGoal::COMPLETED) {
//
//                            //set completed status for goal
//                            $userGoal->setStatus(UserGoal::COMPLETED);
//                        }
//
//                        //set confirm value
//                        $confirm = true;
//                    }
//                }
//                else {
//
//                    //get visible value
//                    $visible = $goalData[$goal->getId()];
//
//                    //create new userGoal for current user
//                    $userGoal = new UserGoal();
//                    $userGoal->setUser($user);
//                    $userGoal->setGoal($goal);
//                    $userGoal->setStatus(UserGoal::COMPLETED);
//                    $userGoal->setIsVisible($visible);
//                    $userGoal->setCompletionDate(new \DateTime('now'));
//                    $userGoal->setConfirmed(true);
//
//                    //set confirm value
//                    $confirm = true;
//                }
//
//                //check if user has confirmed goal
//                if ($confirm) {
//                    $em->persist($userGoal);
//                }
//            }
//
//            $em->flush();
//            $em->clear();
//
//            return new Response('', Response::HTTP_NO_CONTENT);
//        }
//        else {
//            return new Response('Goals not found', Response::HTTP_NOT_FOUND);
//        }
//    }
}