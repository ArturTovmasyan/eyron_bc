<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/28/15
 * Time: 12:15 PM
 */

namespace AppBundle\Controller\Rest;

use AppBundle\Entity\UserGoal;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Rest\RouteResource("Activity")
 */
class NewsFeedController extends FOSRestController
{

    /**
     * @Rest\Get("/api/v2.0/activities/{first}/{count}/{userId}", requirements={"first"="\d+", "count"="\d+", "userId"="\d+"}, defaults={"userId" = null}, name="app_rest_newsfeed_get", options={"method_prefix"=false})
     * @ApiDoc(
     *  resource=true,
     *  section="Activity",
     *  description="This function is used to get goal",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  }
     *
     * )
     *
     * @Rest\View()
     * @Security("has_role('ROLE_USER')")
     *
     * @param $first
     * @param $count
     * @param null $userId
     * @param $request
     *
     * @return Response
     */
    public function getAction($first, $count, $userId = null, Request $request)
    {
        $newsFeeds = $this->getNewsFeed($first, $count, $userId, $request);

        $groups = array("new_feed", "tiny_goal", "images", "successStory", "comment", "successStory_storyImage", "storyImage", "tiny_user");

        $view = $this->view($newsFeeds, 200)
            ->setSerializationContext(SerializationContext::create()->setGroups($groups));

        return $this->handleView($view);
    }

    /**
     * @Rest\Get("/api/v1.0/activities/{first}/{count}", requirements={"first"="\d+", "count"="\d+"}, name="app_rest_newsfeed_get_old", options={"method_prefix"=false})
     * @ApiDoc(
     *  resource=true,
     *  section="Activity",
     *  description="This function is used to get goal",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  }
     *
     * )
     *
     * @Rest\View(serializerGroups={"new_feed", "tiny_goal", "images", "tiny_user", "successStory", "comment", "successStory_storyImage", "storyImage"})
     * @Security("has_role('ROLE_USER')")
     *
     * @param $first
     * @param $count
     * @param $request
     *
     * @return Response
     */
    public function getOldAction($first, $count, Request $request)
    {
        $newsFeeds = $this->getNewsFeed($first, $count, null, $request);

        $oldNewFeeds = [];
        foreach($newsFeeds as $newFeed){
            $count = 0;
            foreach($newFeed->getGoals() as $goal){
                if ($count < 2) {
                    $oldNewFeed = clone $newFeed;
                    $oldNewFeed->setGoals(null);
                    $oldNewFeed->setGoal($goal);

                    $stats = $goal->getStats();
                    $oldNewFeed->setListedBy($stats['listedBy']);
                    $oldNewFeed->setCompletedBy($stats['doneBy']);

                    $oldNewFeeds[] = $oldNewFeed;
                    $count++;
                }
                else {
                    break;
                }
            }
        }

        return $oldNewFeeds;
    }

    /**
     * @param $first
     * @param $count
     * @param null $userId
     * @param Request $request
     * @return \Doctrine\ORM\Query|mixed
     */
    private function getNewsFeed($first, $count, $userId = null, Request $request)
    {
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();
        $em = $this->getDoctrine()->getManager();

        $lastId   = $request->query->get('id', null);
        if (!is_null($lastId) && !is_numeric($lastId)){
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        $lastDate = $request->query->get('time', null);
        //Only for date validation
        if (!is_null($lastDate)){
            try {
                new \DateTime($lastDate);
            }
            catch(\Exception $e) {
                throw new HttpException(Response::HTTP_BAD_REQUEST);
            }
        }

        //If user is logged in then show news feed
        $newsFeeds = $em->getRepository('AppBundle:NewFeed')
            ->findNewFeed($this->getUser()->getId(), null, $first, $count, $lastId, $lastDate, $userId);

        $userGoalsArray = $em->getRepository('AppBundle:UserGoal')->findUserGoals($this->getUser()->getId());

        $liipManager = $this->get('liip_imagine.cache.manager');
        $route = $this->get('router');
        foreach($newsFeeds as $newsFeed){
            foreach($newsFeed->getGoals() as $goal)
            {
                if ($goal->getImagePath()) {
                    try {

                        $liipManager->getBrowserPath($goal->getImagePath(), 'mobile_goal');
                        $params = ['path' => ltrim($goal->getImagePath(), '/'), 'filter' => 'mobile_goal'];
                        $filterUrl = $route->generate('liip_imagine_filter', $params);
                        $goal->setMobileImagePath($filterUrl);

                        $goal->setCachedImage($liipManager->getBrowserPath($goal->getListPhotoDownloadLink(), 'goal_list_horizontal'));
                    } catch (\Exception $e) {
                        $goal->setCachedImage("");
                    }
                }

                if (count($userGoalsArray) > 0) {
                    if (array_key_exists($goal->getId(), $userGoalsArray)) {
                        $goal->setIsMyGoal($userGoalsArray[$goal->getId()]['status'] == UserGoal::COMPLETED ? UserGoal::COMPLETED : UserGoal::ACTIVE);
                    } else {
                        $goal->setIsMyGoal(0);
                    }
                }
            }

            if ($newsFeed->getUser()->getImagePath()) {
                try {
                    $newsFeed->getUser()->setCachedImage($liipManager->getBrowserPath($newsFeed->getUser()->getImagePath(), 'user_icon'));
                } catch (\Exception $e) {
                    $newsFeed->getUser()->setCachedImage("");
                }
            }
        }

        return $newsFeeds;
    }
}