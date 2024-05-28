<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/28/15
 * Time: 12:15 PM
 */

namespace AppBundle\Controller\Rest;

use AppBundle\Entity\Goal;
use AppBundle\Entity\UserGoal;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\RouteResource("UserGoal")
 */
class UserGoalController extends FOSRestController
{
    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="UserGoal",
     *  description="This function is used to set invisible all user goals",
     *  statusCodes={
     *         200="OK",
     *     },
     * )
     * @Rest\Get("/api/v1.0/usergoals/invisible-all", name="get_usergoal_invisible_all", options={"method_prefix"=false})
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function getInvisibleAllUserGoalsAction()
    {
        //get current user
        $user = $this->getUser();

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        $em->getRepository("AppBundle:UserGoal")->setInvisibleAllUserGoals($user->getId());


        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="UserGoal",
     *  description="This function is used to get userGoal",
     *  statusCodes={
     *         200="Returned when userGoal was returned",
     *         401="Returned when user not found",
     *         404="UserGoal not found"
     *     },
     *
     * )
     *
     * @Rest\Get("/api/v1.0/usergoals/{goal}", name="rest_get_usergoal", options={"method_prefix"=false})
     * @Rest\View(serializerGroups={"userGoal", "userGoal_location", "userGoal_goal", "goal", "goal_author", "user", "tiny_goal"})
     * @Security("has_role('ROLE_USER')")
     *
     * @param $goal Goal
     * @return Response
     */
    public function getAction(Goal $goal)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository("AppBundle:Goal")->findGoalStateCount($goal);
        $userGoal = $em->getRepository("AppBundle:UserGoal")->findByUserAndGoal($this->getUser()->getId(), $goal->getId());

        if(!$userGoal){
            $userGoal = new UserGoal();
            $userGoal->setGoal($goal);
        }

        $liipManager = $this->get('liip_imagine.cache.manager');
        if ($userGoal->getGoal()->getListPhotoDownloadLink()){
            $userGoal->getGoal()->setCachedImage($liipManager->getBrowserPath($userGoal->getGoal()->getListPhotoDownloadLink(), 'goal_list_big'));
        }

        return $userGoal;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="UserGoal",
     *  description="This function is used to create or update userGoal",
     *  statusCodes={
     *         200="Returned when userGoal was returned",
     *         401="Returned when user not found",
     *         404="Goal not found"
     *     },
     *  parameters={
     *      {"name"="goal_status", "dataType"="boolean", "required"=false, "description"="ACTIVE:false or COMPLETED:true"},
     *      {"name"="is_visible", "dataType"="boolean", "required"=false, "description"="true / false"},
     *      {"name"="note", "dataType"="string", "required"=false, "description"="note"},
     *      {"name"="steps[write step text here]", "dataType"="boolean", "required"=false, "description"="steps"},
     *      {"name"="location[address]", "dataType"="string", "required"=false, "description"="address"},
     *      {"name"="location[latitude]", "dataType"="float", "required"=false, "description"="latitude"},
     *      {"name"="location[longitude]", "dataType"="float", "required"=false, "description"="longitude"},
     *      {"name"="urgent", "dataType"="boolean", "required"=false, "description"="Urgent boolean"},
     *      {"name"="important", "dataType"="boolean", "required"=false, "description"="Important boolean"},
     *      {"name"="do_date", "dataType"="date", "required"=false, "description"="do date with d/m/Y format"},
     *      {"name"="completion_date", "dataType"="date", "required"=false, "description"="completion date with d/m/Y format"},
     *      {"name"="date_status", "dataType"="integer", "required"=false, "description"="completion date status OLL = 1; ONLY_YEAR = 2; ONLY_YEAR_MONTH = 3;"},
     *      {"name"="do_date_status", "dataType"="integer", "required"=false, "description"="do date status OLL = 1; ONLY_YEAR = 2; ONLY_YEAR_MONTH = 3;"},
     * }
     * )
     *
     * @Rest\Put("/api/v1.0/usergoals/{goal}", name="rest_put_usergoal", options={"method_prefix"=false})
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("goal", class="AppBundle:Goal", options={"repository_method" = "findWithRelations"})
     * @Rest\View(serializerGroups={"userGoal", "userGoal_location", "userGoal_goal", "goal", "goal_author", "tiny_goal", "tiny_user"})
     *
     * @param Goal $goal
     * @param Request $request
     * @return Response
     */
    public function putAction(Goal $goal, Request $request)
    {
        $this->denyAccessUnlessGranted('add', $goal, $this->get('translator')->trans('goal.add_access_denied'));

        $this->getDoctrine()->getManager()->getRepository("AppBundle:Goal")->findGoalStateCount($goal);
        $userGoalService = $this->get('app.user.goal');
        return $userGoalService->addUserGoal($request, $goal, $this->getUser());
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="UserGoal",
     *  description="This function is used to remove userGoal",
     *  statusCodes={
     *         200="Returned when userGoal was removed",
     *         401="User not found else it isn't users userGoal",
     *         404="UserGoal not found"
     *     },
     *
     * )
     *
     * @Rest\Delete("/api/v1.0/usergoals/{userGoal}", name="rest_delete_usergoal", options={"method_prefix"=false})
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     * @param $userGoal
     * @return Response
     */
    public function deleteAction(Request $request, $userGoal)
    {
        $request->getSession()->getFlashBag()->get('success');

        $userGoalService = $this->get('app.user.goal');
        $msg = $userGoalService->deleteUserGoal($userGoal, $this->getUser());

        return new Response($msg, Response::HTTP_OK);
    }

    /**
     * @param $value
     * @return bool
     */
    private function toBool($value){
        if ($value === 'true' || $value === true || $value === 1){
            return true;
        }

        return false;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="UserGoal",
     *  description="This function is used to get my bucketlist",
     *  statusCodes={
     *         200="Returned when all ok"
     *     },
     *
     *  parameters={
     *      {"name"="condition", "dataType"="integer", "required"=false, "description"="ACTIVE:1 or COMPLETED:2"},
     *      {"name"="first", "dataType"="integer", "required"=false, "description"="first number of user Goal"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="count of userGoals results"},
     *      {"name"="isDream", "dataType"="boolean", "required"=true, "description"="Status boolean"},
     *      {"name"="urgentImportant", "dataType"="boolean", "required"=false, "description"="Status boolean"},
     *      {"name"="urgentNotImportant", "dataType"="boolean", "required"=false, "description"="Status boolean"},
     *      {"name"="notUrgentImportant", "dataType"="boolean", "required"=false, "description"="Status boolean"},
     *      {"name"="notUrgentNotImportant", "dataType"="boolean", "required"=false, "description"="Status boolean"},
     *      {"name"="userId", "dataType"="integer", "required"=false, "description"="User id"},
     * })
     *
     * @Rest\Post("/api/v1.0/usergoals/bucketlists", name="post_usergoal_bucketlist", options={"method_prefix"=false})
     * @Rest\Get("/api/v2.0/usergoals/bucketlists", name="get_usergoal_bucketlist", options={"method_prefix"=false})
     * @Rest\Post("/api/v1.0/usergoals/locations", name="rest_post_usergoal_locations", options={"method_prefix"=false})
     * @Rest\View(serializerGroups={"userGoal", "userGoal_goal", "goal", "goal_author", "tiny_user"})
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function postBucketlistAction(Request $request)
    {
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();

        if($request->getContentType() == 'application/json' || $request->getContentType() == 'json'){
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        $em        = $this->getDoctrine()->getManager();
        $userId    = $request->get('userId');
        $user      = $userId ? $em->getRepository('ApplicationUserBundle:User')->find($userId) : $this->getUser();

        // check conditions
        switch($request->get('condition')){
            case UserGoal::ACTIVE:
                $condition = UserGoal::ACTIVE;
                break;
            case UserGoal::COMPLETED:
                $condition = UserGoal::COMPLETED;
                break;
            default:
                $condition = null;
        }

        $dream = $this->toBool($request->query->get('isDream'));
        $first = $request->get('first');
        $count = $request->get('count');

        $requestFilter = [];
        $requestFilter[UserGoal::URGENT_IMPORTANT]          = $this->toBool($request->get('urgentImportant'));
        $requestFilter[UserGoal::URGENT_NOT_IMPORTANT]      = $this->toBool($request->get('urgentNotImportant'));
        $requestFilter[UserGoal::NOT_URGENT_IMPORTANT]      = $this->toBool($request->get('notUrgentImportant'));
        $requestFilter[UserGoal::NOT_URGENT_NOT_IMPORTANT]  = $this->toBool($request->get('notUrgentNotImportant'));

        $response = new Response();

        if ($request->get('_route') == 'get_usergoal_bucketlist')
        {
            $lastUpdated = $em->getRepository('AppBundle:UserGoal')
                ->findAllByUser($user->getId(), $condition, $dream, $requestFilter, $first, $count, true);

            if (is_null($lastUpdated)) {
                return ['user_goals' => [], 'user' => $user];
            }

            $lastDeleted = $user->getUserGoalRemoveDate();
            $lastModified = $lastDeleted > $lastUpdated ? $lastDeleted: $lastUpdated;

            $response->setLastModified($lastModified);
//            $response->headers->set('ETag', $data['etag']);
            $response->headers->set('cache-control', 'private, must-revalidate');

            if ($response->isNotModified($request)) {
                return $response;
            }
        }


        $userGoals = $em->getRepository('AppBundle:UserGoal')
            ->findAllByUser($user->getId(), $condition, $dream, $requestFilter, $first, $count);

        //This part is used to calculate goal stats
        $goalIds   = [];
        $authorIds = [];
        foreach($userGoals as $userGoal){
            $goalIds[$userGoal->getGoal()->getId()] = 1;
            if ($userGoal->getGoal()->getAuthor()) {
                $authorIds[] = $userGoal->getGoal()->getAuthor()->getId();
            }
        }

        $goalStats = $em->getRepository("AppBundle:Goal")->findGoalStateCount($goalIds, true);
        $authorstats = $em->getRepository("ApplicationUserBundle:User")->findUsersStats($authorIds);

        foreach($userGoals as $userGoal){
            $userGoal->getGoal()->setStats([
                'listedBy' => $goalStats[$userGoal->getGoal()->getId()]['listedBy'],
                'doneBy'   => $goalStats[$userGoal->getGoal()->getId()]['doneBy'],
            ]);

            if ($userGoal->getGoal()->getAuthor()) {
                $stats = $authorstats[$userGoal->getGoal()->getAuthor()->getId()];
                $userGoal->getGoal()->getAuthor()->setStats([
                    "listedBy" => $stats['listedBy'] + $stats['doneBy'],
                    "active"   => $stats['listedBy'],
                    "doneBy"   => $stats['doneBy']
                ]);
            }
        }

        $em->getRepository('ApplicationUserBundle:User')->setUserStats($user);

        $liipManager = $this->get('liip_imagine.cache.manager');
        foreach ($userGoals as $userGoal) {
            if ($userGoal->getGoal()->getListPhotoDownloadLink()) {
                try {
                    $userGoal->getGoal()
                        ->setCachedImage($liipManager->getBrowserPath($userGoal->getGoal()->getListPhotoDownloadLink(),
                                                                      $this->getUser()->getId() == $userId ? 'goal_bucketlist' : 'goal_list_horizontal'));
                } catch (\Exception $e) {
                    $userGoal->getGoal()->setCachedImage("");
                }
            }
        }

        $serializer = $this->get('serializer');
        $onlyPublish = (is_null($userId) || $userId == $this->getUser()->getId()) ? false : true;

        $states = $user->getStats();
        $states['created'] = $em->getRepository('AppBundle:Goal')->findOwnedGoalsCount($user->getId(), $onlyPublish);
        $user->setStats($states);

        if ($request->get('_route') != 'rest_post_usergoal_locations'){
            if ($user->getId() != $this->getUser()->getId()){
                $commonCounts = $em->getRepository('AppBundle:Goal')->findCommonCounts($this->getUser()->getId(), [$user->getId()]);
                $user->setCommonGoalsCount($commonCounts[$user->getId()]['commonGoals']);
            }
            $content = ['user_goals' => $userGoals, 'user' => $user];
            $serializedContent = $serializer->serialize($content, 'json',
                SerializationContext::create()->setGroups(array("userGoal", "userGoal_goal", "goal", "goal_author", "tiny_user")));
        }
        else {
            $serializedContent = $serializer->serialize($userGoals, 'json',
                SerializationContext::create()->setGroups(array("userGoal_location", "tiny_user", "userGoal_goal", "tiny_goal")));
        }

        $response->setContent($serializedContent);

        return $response;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="UserGoal",
     *  description="This function is used to get bucketlist goals locations",
     *  statusCodes={
     *         200="Returned when all ok"
     *     },
     *
     *  parameters={
     *      {"name"="condition", "dataType"="integer", "required"=false, "description"="ACTIVE:1 or COMPLETED:2"},
     *      {"name"="first", "dataType"="integer", "required"=false, "description"="first number of user Goal"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="count of userGoals results"},
     *      {"name"="isDream", "dataType"="boolean", "required"=true, "description"="Status boolean"},
     *      {"name"="urgentImportant", "dataType"="boolean", "required"=false, "description"="Status boolean"},
     *      {"name"="urgentNotImportant", "dataType"="boolean", "required"=false, "description"="Status boolean"},
     *      {"name"="notUrgentImportant", "dataType"="boolean", "required"=false, "description"="Status boolean"},
     *      {"name"="notUrgentNotImportant", "dataType"="boolean", "required"=false, "description"="Status boolean"},
     *      {"name"="userId", "dataType"="integer", "required"=false, "description"="User id"},
     * }
     *
     * )
     *
     * @Rest\Post("/api/v1.0/usergoals/locations", name="rest_post_usergoal_locations", options={"method_prefix"=false})
     * @Rest\View(serializerGroups={"userGoal_location", "tiny_user", "userGoal_goal", "tiny_goal"})
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
//    public function postLocationsAction(Request $request)
//    {
//        $em        = $this->getDoctrine()->getManager();
//        $userId    = $request->get('userId');
//        $user      = $userId ? $em->getRepository('ApplicationUserBundle:User')->find($userId) : $this->getUser();
//
//        // check conditions
//        switch($request->query->get('condition')){
//            case UserGoal::ACTIVE:
//                $condition = UserGoal::ACTIVE;
//                break;
//            case UserGoal::COMPLETED:
//                $condition = UserGoal::COMPLETED;
//                break;
//            default:
//                $condition = null;
//        }
//
//        $dream = $this->toBool($request->query->get('isDream'));
//        $first = $request->query->get('first');
//        $count = $request->query->get('count');
//
//        $requestFilter = [];
//        $requestFilter[UserGoal::URGENT_IMPORTANT]          = $this->toBool($request->query->get('urgentImportant'));
//        $requestFilter[UserGoal::URGENT_NOT_IMPORTANT]      = $this->toBool($request->query->get('urgentNotImportant'));
//        $requestFilter[UserGoal::NOT_URGENT_IMPORTANT]      = $this->toBool($request->query->get('notUrgentImportant'));
//        $requestFilter[UserGoal::NOT_URGENT_NOT_IMPORTANT]  = $this->toBool($request->query->get('notUrgentNotImportant'));
//
//
//        $userGoals = $em->getRepository('AppBundle:UserGoal')
//            ->findAllByUser($user->getId(), $condition, $dream, $requestFilter, $first, $count);
//
//        return $userGoals;
//    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="UserGoal",
     *  description="This function is used to done or active userGoal (isDone = 1 for completed and 0 to set as active)",
     *  statusCodes={
     *         200="Returned when userGoal was done or activated",
     *         401="Returned when user not found",
     *         404="Returned when goal not found"
     *     }
     * )
     *
     * @Rest\Get("/api/v1.0/usergoals/{goal}/dones/{isDone}", name="rest_get_usergoal_done", options={"method_prefix"=false})
     * @Security("has_role('ROLE_USER')")
     *
     * @param Goal $goal
     * @param $isDone
     * @return Response
     */
    public function getDoneAction(Goal $goal, $isDone = null)
    {
        $this->denyAccessUnlessGranted('done', $goal, $this->get('translator')->trans('goal.add_access_denied'));

        $userGoalService = $this->get('app.user.goal');

        $newDone = $userGoalService->doneBy($goal, $this->getUser(), $isDone);

        return new Response((int) $newDone, Response::HTTP_OK);
    }

    /**
     * This function is used to get active, completed userGoal counts by date
     *
     * @ApiDoc(
     *  resource=true,
     *  section="UserGoal",
     *  description="This function is used to get active, completed userGoal counts by date",
     *  statusCodes={
     *         200="Returned when all ok",
     *     }
     * )
     * @Rest\Get("/api/v1.0/usergoal/calendar/data")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function getCalendarData()
    {
        //get current user
        $user = $this->getUser();

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get userGoal type count for calendar data
        $userGoals = $em->getRepository('AppBundle:UserGoal')->findAllForCalendar($user->getId());

        //set default values
        $calendarData = [];
        $completionCount = 0;
        $activeCount = 0;

        foreach ($userGoals as $userGoal)
        {
            //create completion data in array by date
            if (isset($userGoal['completionDate']) || (isset($userGoal['completionDate']) && isset($userGoal['doDate']))) {

                //get completion date in array
                $completionDate = $userGoal['completionDate']->format('Y-m-d');

                if (array_key_exists($completionDate, $calendarData)) {

                    if (isset($calendarData[$completionDate]['completion'])) {
                        $calendarData[$completionDate]['completion'] += 1;
                    }
                    else {
                        $calendarData[$completionDate]['completion'] = 1;
                    }
                }
                else {
                    $calendarData[$completionDate]['completion'] = $completionCount + 1;
                }
            }

            //create active data in array by date
            if (isset($userGoal['doDate']) && (!isset($userGoal['completionDate']))) {

                //get due date in array
                $doDate = $userGoal['doDate']->format('Y-m-d');

                if (array_key_exists($doDate, $calendarData)) {

                    if (isset($calendarData[$doDate]['active'])) {
                        $calendarData[$doDate]['active'] += 1;
                    }
                    else {
                        $calendarData[$doDate]['active'] = 1;
                    }
                }
                else {
                    $calendarData[$doDate]['active'] = $activeCount + 1;
                }
            }
        }

        return new JsonResponse($calendarData);
    }
    /**
     * This function is used to toggle not interested
     *
     * @ApiDoc(
     *  resource=true,
     *  section="UserGoal",
     *  description="This function is used to toggle not interested",
     *  statusCodes={
     *         200="Returned when all ok",
     *     }
     * )
     * @Security("has_role('ROLE_USER')")
     *
     * @param Goal $goal
     * @param Request $request
     * @return JsonResponse
     */
    public function postToggleInterestedAction(Goal $goal, Request $request)
    {
        $this->denyAccessUnlessGranted('add', $goal, $this->get('translator')->trans('goal.add_access_denied'));

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

         // get user goal
         $userGoal = $em->getRepository("AppBundle:UserGoal")->findByUserAndGoal($user->getId(), $goal->getId());

         if (!$userGoal) {
             $userGoal = new UserGoal();
             $userGoal->setGoal($goal);
             $userGoal->setUser($user);
             $userGoal->setImportant(false);
             $userGoal->setStatus(0);
             $userGoal->setIsVisible(false);
             $notInterested = true;
         }
         else{
                $notInterested = !$userGoal->getNotInterested();
            }

         $userGoal->setNotInterested($notInterested);

         $em->persist($userGoal);
         $em->flush();

         return new JsonResponse(null, Response::HTTP_OK);
     }
}