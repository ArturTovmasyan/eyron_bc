<?php

namespace Application\UserBundle\Controller\Rest;

use Application\UserBundle\Entity\Badge;
use Application\UserBundle\Services\BadgeService;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BadgeController
 * @package Application\UserBundle\Controller\Rest
 *
 * @Rest\RouteResource("Badge")
 * @Rest\Prefix("/api/v1.0")
 */

class BadgeController extends Controller
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Badge",
     *  description="This function is used to get TOP 100 users by badge score",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *         400="Bad request"
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"badge"})
     * @return mixed
     */
    public function cgetAction()
    {
        // get listener
        $this->get('bl.doctrine.listener')->disableUserStatsLoading();

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        $lastUpdateString = $em->getRepository("ApplicationUserBundle:Badge")->getMinUpdated();
        $lastUpdate = new \DateTime($lastUpdateString);

        $badgeService = $this->get('bl.badge.service'); // get badge service

        $badges = $this->get('bl.apc')->apc_fetch(BadgeService::TOP_BADGES_USERS);
        if(!$badges){
            $badges = $badgeService->findTopUsers();
            return $badges;
        }

        $cacheMaxUpdate = array_key_exists('maxUpdate', $badges) ? $badges['maxUpdate'] : null;
        if($lastUpdate > $cacheMaxUpdate){
            $badges = $badgeService->findTopUsers();
        }

       return $badges;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Badge",
     *  description="This function is used to get TOP 100 users by badge score",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *         400="Bad request"
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"badge"})
     * @param $type
     * @param $count
     * @return mixed
     */
    public function getTopuserAction($type, $count)
    {
        //check if one is parameters not exists
        if(!is_numeric($type) || !is_numeric($count) ) {
            return new Response('Invalid link parameter', Response::HTTP_BAD_REQUEST);
        }

        //check if count great then 100
        $count = $count > 100 ? 100 : $count;
        
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // get listener
        $this->get('bl.doctrine.listener')->disableUserStatsLoading();

        //get top users
        $userId = is_object($this->getUser()) ? $this->getUser()->getId() : null;
        $badges = $em->getRepository('ApplicationUserBundle:Badge')->findTopUsersByType($type, $count, $userId);

        if($badges){
            $maxScoreBadge = reset($badges);
            $maxScore = $maxScoreBadge->getScore();

            // map for values and normalize scores
            array_map(function($badge) use ($maxScore){
                $normalizedScore = $badge->getScore()/$maxScore * Badge::MAXIMUM_NORMALIZE_SCORE;
                $normalizedScore = ceil($normalizedScore);
                $badge->normalizedScore = $normalizedScore;
            }, $badges);
        }

        return $badges;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Badge",
     *  description="This function is used to normalizer score data for badges",
     *  statusCodes={
     *         200="Returned when max score returned",
     *         400="Bad request"
     *  }
     * )
     *
     * @Rest\View()
     * @param Request $request
     * @return mixed
     */
    public function putMaxScoreAction(Request $request)
    {
        //check if request content type is json
        if ($request->getContentType() == 'application/json' || $request->getContentType() == 'json') {

            //get content and add it in request after json decode
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        //get badges
        $badges = $request->request->get('badges');

        //check if badges not exists
        if(!$badges) {
            return new Response('Invalid request data', Response::HTTP_BAD_REQUEST);
        }

        //normalizer badges score data
       $newBadges = array_map(function($item) {

            // get max badge score
            $maxBadgeScore = $this->get('bl.badge.service')->getMaxScore($item['points'], $item['type']);

            //get normalizer score data
            $maxScore = array_key_exists($item['type'], $maxBadgeScore) ? $maxBadgeScore[$item['type']] : $item['points'];
            $normalizedScore = ($item['points']/$maxScore) * Badge::MAXIMUM_NORMALIZE_SCORE;
            $normalizedScore = ceil($normalizedScore);
            $item['points'] = $normalizedScore;

            return $item;

        }, $badges);

        return $newBadges;
    }
}