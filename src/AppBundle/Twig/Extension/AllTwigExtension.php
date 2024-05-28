<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/27/15
 * Time: 11:10 AM
 */

namespace AppBundle\Twig\Extension;

use Application\UserBundle\Entity\Badge;
use Symfony\Component\DependencyInjection\Container;
use AppBundle\Entity\Goal;
use AppBundle\Entity\NewFeed;
use AppBundle\Entity\UserGoal;

/**
 * Class AllTwigExtension
 * @package AppBundle\Twig\Extension
 */
class AllTwigExtension extends \Twig_Extension implements \Twig_Extension_InitRuntimeInterface
{
    /**
     * @var
     */
    protected $environment;

    /**
     * @var Container
     */
    protected $container;

    /**
     * OllTwigExtension constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->session   = $container->get('session');
    }

    /**
     * @param \Twig_Environment $environment
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('sliceString', array($this, 'sliceString')),
            new \Twig_SimpleFilter('removeTag', array($this, 'removeTag')),
            new \Twig_SimpleFilter('remove_asset_version', array($this, 'removeAssetVersion'),  array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('json_decode', array($this, 'json_decode'))
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('replaceString', array($this, 'replaceString')),
            new \Twig_SimpleFunction('getNewAction', array($this, 'getNewAction')),
            new \Twig_SimpleFunction('isMobile', array($this, 'isMobile')),
            new \Twig_SimpleFunction('isTablet', array($this, 'isTablet')),
            new \Twig_SimpleFunction('getSession', array($this, 'getSession')),
            new \Twig_SimpleFunction('locations', array($this, 'locations')),
            new \Twig_SimpleFunction('getReferer', array($this, 'getReferer')),
            new \Twig_SimpleFunction('getThreadInnerLink', array($this, 'getThreadInnerLink')),
            new \Twig_SimpleFunction('badgeNormalizer', array($this, 'badgeNormalizer'))
        );
    }


    /**
     * @param $json
     * @return mixed
     */
    public function json_decode($json)
    {
        $content = json_decode($json, true);
        return $content;
    }

    /**
     * @param $search
     * @param $replace
     * @param $object
     * @return mixed
     */
    public function replaceString($search, $replace, $object)
    {
        $content = str_replace($search, $replace, $object);
        return $content;
    }

    /**
     * @param $actionCode
     * @return mixed
     */
    public function getNewAction($actionCode)
    {
        $translator = $this->container->get('translator');
        if ($actionCode == NewFeed::GOAL_CREATE){
            return $translator->trans('goal.create', array(), 'newsFeed');
        }
        if ($actionCode == NewFeed::GOAL_ADD){
            return $translator->trans('goal.add', array(), 'newsFeed');
        }
        if ($actionCode == NewFeed::GOAL_COMPLETE){
            return $translator->trans('goal.complete', array(), 'newsFeed');
        }
        if ($actionCode == NewFeed::SUCCESS_STORY){
            return $translator->trans('goal.success_story', array(), 'newsFeed');
        }
        if ($actionCode == NewFeed::COMMENT){
            return $translator->trans('goal.comment', array(), 'newsFeed');
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isMobile()
    {
        return $this->container->get('mobile_detect.mobile_detector')->isMobile();
    }

    /**
     * @return bool
     */
    public function isTablet()
    {
        return $this->container->get('mobile_detect.mobile_detector')->isTablet();
    }

    /**
     * @param $text
     * @return mixed
     */
    public function removeTag($text)
    {
        $content = preg_replace('/#([^\s#])/', '$1',  $text);

        return  $content;
    }

    /**
     * @param $text
     * @return mixed
     */
    public function getSession($text)
    {
        //get session
        $session = $this->session;

        //set addUrl default data
        $addUrl = null;

        //if session have data with nam $text
        if($session->has($text)) {

            //get add url in session
            $addUrl = $session->get($text);

            $session->remove($text);
        }

        return $addUrl;
    }

    /**
     * @param $goals
     * @return array
     * @throws \Exception
     */
    public function locations($goals)
    {
        // empty data for return result
        $result = array();

        // check data
        if($goals && is_array($goals)){

            // loop for goals
            foreach($goals as $item){

                // is user goal
                if($item instanceof UserGoal){
                    $id = $item->getGoal()->getId();
                    $goal = $item->getGoal();
                }
                // is goal
                elseif ($item instanceof Goal){
                    $id = $item->getId();
                    $goal = $item;
                }
                else {
                    throw new \Exception("Error");
                }

                $result[$id] = array(
                    'title' => $goal->getTitle(),
                    'latitude' => $goal->getLat(),
                    'longitude' => $goal->getLng(),
                    'slug' => $goal->getSlug(),
                    'image' => $goal->getListPhotoDownloadLink(),
                    'status' => $goal->getIsMyGoal()
                );
            }
        }


        return $result;
    }

    /**
     * @param $text
     * @return mixed
     */
    public function sliceString($text)
    {
        //clear html tag and spaces
        $text = strip_tags($text);
        $text = htmlspecialchars(trim($text));

        //check if text less then 160 symbol
        if(strlen($text) > 160) {

            //cut string
            $content = substr($text, 0, 160).".";

            //get last dot position
            $pos = strrpos($content, '.');

            //check if dot not exist
            if(!$pos) {
                $pos = strrpos($content, ',');
            }

            //cut text with dot position
            $content = substr($content, 0, $pos + 1);
        }
        else {
            $content = $text;
        }

        return $content;
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $referer = $request->headers->get('referer');

        if($referer){
            return $referer;
        }
        return "";
    }

    /**
     * @param $threadId
     * @return string
     * @throws \Throwable
     */
    public function getThreadInnerLink($threadId)
    {
        $router = $this->container->get('router');
        if ($startPos = strpos($threadId, 'goal_') === 0){
            $slug = substr($threadId, 5);
        }
        elseif($t = preg_match('^/success_story_[\d+]+_/', $threadId, $matches, PREG_OFFSET_CAPTURE)){
            $slug = preg_replace('^/success_story_[\d+]+_/', '', $threadId);
        }

        return isset($slug) ? $router->generate('inner_goal', ['slug' => $slug]) : '#';
    }


    /**
     * @param $type
     * @param $score
     * @return float
     * @throws \Throwable
     */
    public function badgeNormalizer($type, $score)
    {
        // get max badge score
        $maxBadgeScore = $this->container->get('bl.badge.service')->getMaxScore($score, $type);
        $maxScore = array_key_exists($type, $maxBadgeScore) ? $maxBadgeScore[$type] : $score;
        $normalizedScore = $score/$maxScore * Badge::MAXIMUM_NORMALIZE_SCORE;
        $normalizedScore = ceil($normalizedScore);

        return $normalizedScore;

    }

    /**
     * @param $url
     * @return string
     */
    public function removeAssetVersion($url)
    {
        $pos = strpos($url, '?');

        if($pos){
            $url = substr($url, 0, $pos);
        }

        return $url;
    }

    public function getName()
    {
        return 'bl_all_twig_extensions';
    }
}