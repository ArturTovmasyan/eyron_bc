<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/6/15
 * Time: 2:59 PM
 */

namespace AppBundle\Listener;

use AppBundle\Entity\Goal;
use AppBundle\Entity\GoalImage;
use AppBundle\Entity\NewFeed;
use AppBundle\Entity\SuccessStory;
use AppBundle\Entity\UserGoal;
use AppBundle\Model\ActivityableInterface;
use AppBundle\Model\ImageableInterface;
use AppBundle\Model\PublishAware;
use Application\CommentBundle\Entity\Comment;
use Application\UserBundle\Entity\Badge;
use Application\UserBundle\Entity\User;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Gedmo\Loggable\Entity\LogEntry;
use Symfony\Component\DependencyInjection\Container;

class DoctrineListener
{
    /**
     * @var
     */
    protected $container;

    protected $tokeStorage;

    protected $loadUserStats = true;

    protected $setIsMyGoal = true;


    public function disableUserStatsLoading()
    {
        $this->loadUserStats = false;
    }

    public function enableUserStatsLoading()
    {
        $this->loadUserStats = true;
    }

    public function disableIsMyGoalLoading()
    {
        $this->setIsMyGoal = false;
    }

    public function enableIsMyGoalLoading()
    {
        $this->setIsMyGoal = true;
    }


    /**
     * @param Container $container
     */
    function __construct(Container $container)
    {
        $this->container = $container;
        $this->tokeStorage = $container->get('security.token_storage');
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        //get stopwatch component
        $stopwatch = $this->container->get('debug.stopwatch');

        // Start event named 'eventName'
        $stopwatch->start('bl_post_load_event_for_goal_or_user');

        $em     = $this->container->get('doctrine')->getManager();
        $entity = $event->getObject();

        if ($token = $this->container->get('security.token_storage')->getToken())
        {
            if ($entity instanceof Goal){
                $user = $token->getUser();
                if ($entity->getSlug()) {
                    $shareLink = $this->container->get('router')->generate('inner_goal', array('slug' => $entity->getSlug()));
                    $entity->setShareLink($shareLink);
                }

                //Set goal is_my_goal fields
                if ($user instanceof User && $this->setIsMyGoal) {
                    $userGoalsArray = $em->getRepository('AppBundle:UserGoal')->findUserGoals($user->getId());
                    if (count($userGoalsArray) > 0) {
                        if (array_key_exists($entity->getId(), $userGoalsArray)) {
                            $entity->setIsMyGoal($userGoalsArray[$entity->getId()]['status'] == UserGoal::COMPLETED ? UserGoal::COMPLETED : UserGoal::ACTIVE);
                        } else {
                            $entity->setIsMyGoal(0);
                        }
                    }
                }
            }

            if ($entity instanceof SuccessStory){
                $entity->setIsVote($token->getUser());
            }

            //Set user stats
            elseif ($entity instanceof User){
                if ($this->loadUserStats){
                    $em->getRepository('ApplicationUserBundle:User')->setUserStats($entity);
                }
            }
        }

        //Set Cached image paths
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $route = $this->container->get('router');
        $liipManager = $this->container->get('liip_imagine.cache.manager');

        if ($entity instanceof ImageableInterface){
            if ($request && $request->get('_format') == "json" && $entity->getImagePath()){
                $liipManager->getBrowserPath($entity->getImagePath(), 'mobile_goal');
                $params = ['path' => ltrim($entity->getImagePath(), '/'), 'filter' => 'mobile_goal'];
                $filterUrl = $route->generate('liip_imagine_filter', $params);
                $entity->setMobileImagePath($filterUrl);
            }
        }
        elseif($entity instanceof User){
            if ($request && $request->get('_format') == "json" ){
                if ($entity->getImagePath()){
                    $liipManager->getBrowserPath($entity->getImagePath(), 'user_goal');
                    $params = ['path' => ltrim($entity->getImagePath(), '/'), 'filter' => 'user_icon'];
                    $filterUrl = $route->generate('liip_imagine_filter', $params);
                    $entity->setMobileImagePath($filterUrl);
                }
                
                if($token && ($user = $token->getUser()) && is_object($user)){
                    $followings = $user->getFollowingIds();
                    if($followings){
                        $entity->setIsFollow(in_array($entity->getId(), $followings));
                    } else {
                        $entity->setIsFollow(false);
                    }
                }
            }
        }

        // Start event named 'eventName'
         $stopwatch->stop('bl_post_load_event_for_goal_or_user');
    }


    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        //get stopwatch component
        $stopwatch = $this->container->get('debug.stopwatch');

        // Start event named 'eventName'
        $stopwatch->start('bl_on_flush_event_for_user');

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if($entity instanceof User){
                $this->setLocale($entity);
                $entity->setIsAdmin($entity->hasRole('ROLE_ADMIN') || $entity->hasRole('ROLE_SUPER_ADMIN') || $entity->hasRole('ROLE_GOD'));

                //check if user don't have uId
                if(!$entity->getUId()) {
                    do {
                        $string = $this->container->get('bl_random_id_service')->randomString()?$this->container->get('bl_random_id_service')->randomString():"";
                        $isUser = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('uId' => $string));
                    }
                    while($isUser);

                    $entity->setUId($string);
                }
                $metadata = $em->getMetadataFactory()->getMetadataFor('ApplicationUserBundle:User');
                $uow->recomputeSingleEntityChangeSet($metadata, $entity);
            }
        }

        // for update
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            // check entity
            if($entity instanceof User){
                $this->setLocale($entity);
                $entity->setIsAdmin($entity->hasRole('ROLE_ADMIN') || $entity->hasRole('ROLE_SUPER_ADMIN') || $entity->hasRole('ROLE_GOD'));
            }
        }

        // Start event named 'eventName'
        $stopwatch->stop('bl_on_flush_event_for_user');
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        //get stopwatch component
        $stopwatch = $this->container->get('debug.stopwatch');

        // Start event named 'eventName'
        $stopwatch->start('bl_post_update_event_for_userGoal');

        $entity = $event->getObject();
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        $token = $this->tokeStorage->getToken();
        $user = null;
        if ($token){
            $user = $token->getUser();
        }

        if (is_object($user)) {

            if ($entity instanceof UserGoal){

                $changeSet = $uow->getEntityChangeSet($entity);
                if (isset($changeSet['status'])){
                    $goal = $entity->getGoal();
                    if($changeSet['status'][1] == UserGoal::COMPLETED && $entity->getIsVisible() && $goal->getPublish()) {

                        $em->getRepository("AppBundle:Goal")->findGoalStateCount($goal);
                        $newFeed = $em->getRepository("AppBundle:NewFeed")->findLastGroupByUserAction($user->getId(), NewFeed::GOAL_COMPLETE);
                        if (is_null($newFeed)) {
                            $newFeed = new NewFeed(NewFeed::GOAL_COMPLETE, $user, $goal);
                        }
                        else {
                            $newFeed->addGoal($goal);
                        }

                        $em->persist($newFeed);
                        $em->flush();
                    }
                }

                if (isset($changeSet['isVisible'])){
                    if($changeSet['isVisible'][1] == true && $entity->getGoal()->getPublish()) {
                        $goal = $entity->getGoal();
                        $em->getRepository("AppBundle:Goal")->findGoalStateCount($goal);
                        $newFeed = $em->getRepository("AppBundle:NewFeed")->findLastGroupByUserAction($user->getId(), NewFeed::GOAL_ADD);
                        if (is_null($newFeed)) {
                            $newFeed = new NewFeed(NewFeed::GOAL_ADD, $user, $goal);
                        }
                        else {
                            $newFeed->addGoal($goal);
                        }

                        $em->persist($newFeed);
                        $em->flush();
                    }
                }
            }
        }

        // Start event named 'eventName'
        $stopwatch->stop('bl_post_update_event_for_userGoal');
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        //get stopwatch component
        $stopwatch = $this->container->get('debug.stopwatch');

        // Start event named 'eventName'
        $stopwatch->start('bl_post_persist_event_for_goal_or_story_or_userGoal');

        $entity = $event->getObject();
        $em = $event->getObjectManager();

        if ($entity instanceof ActivityableInterface){
            $newFeed = $this->generateActivityOnInsert($em, $entity);
            if (!is_null($newFeed)) {
                $em->persist($newFeed);
                $em->flush();
            }
        }
        // Start event named 'eventName'
        $stopwatch->stop('bl_post_persist_event_for_goal_or_story_or_userGoal');
    }

    /**
     * @param $em
     * @param $entity
     * @return NewFeed|null
     */
    private function generateActivityOnInsert($em, $entity)
    {
        $token = $this->tokeStorage->getToken();
        $user = null;
        if ($token){
            $user = $token->getUser();
        }
        if (is_object($user)){
            $action = $goal = $story = $comment = null;
            if($entity instanceof UserGoal &&
                (is_null($entity->getGoal()->getAuthor()) || $entity->getGoal()->getAuthor()->getId() != $user->getId()))
            {
                $action = NewFeed::GOAL_ADD;
                if ($entity->getStatus() == UserGoal::COMPLETED){
                    $action = NewFeed::GOAL_COMPLETE;
                }

                $goal = $entity->getGoal();
            }
            elseif($entity instanceof SuccessStory && str_replace(" ", "", $entity->getStory()) != "" && $entity->getGoal()->getPublish()){
                $action = NewFeed::SUCCESS_STORY;
                $goal = $entity->getGoal();
                $story = $entity;
            }
            elseif($entity instanceof Comment){
                $threadId = $entity->getThread()->getId();
                if ($startPos = strpos($threadId, 'goal_') === 0){
                    $slug = substr($threadId, 5);
                    $goal = $em->getRepository('AppBundle:Goal')->findOneBySlug($slug);
                    if (!is_null($goal) && $goal->getPublish()){
                        $comment = $entity;
                        $action = NewFeed::COMMENT;
                    }
                }
            }

            if (!is_null($action))
            {
                $userGoal = $em->getRepository("AppBundle:UserGoal")->findByUserAndGoal($user->getId(), $goal->getId());
                if (!is_null($userGoal) && !$userGoal->getIsVisible()){
                    return null;
                }

                $em->getRepository("AppBundle:Goal")->findGoalStateCount($goal);
                if (in_array($action, NewFeed::$groupedActions)){
                    $newFeed = $em->getRepository("AppBundle:NewFeed")->findLastGroupByUserAction($user->getId(), $action);
                }

                if (!isset($newFeed) || is_null($newFeed)){
                    $newFeed = new NewFeed($action, $user, $goal, $story, $comment);
                }
                else {
                    $newFeed->addGoal($goal);
                }

                return $newFeed;
            }
        }

        return null;
    }

    /**
     * @param $entity
     */
    private  function setLocale($entity)
    {
        // get environment
        $env = $this->container->get('kernel')->getEnvironment();
        if($env != "test"){

            try{
                $request = $this->container->get('request_stack')->getCurrentRequest();

                if (is_null($request) || is_null($session = $request->getSession())){
                    return;
                }

                $locale = $session->get("_locale");
                $userLocale = $entity->getLanguage();

                // check user local with default locale
                if($userLocale && $userLocale != $locale){
                    // set session locale
                    $session->set('_locale', $userLocale);
                }
            }
            catch(\Exception $e){
                // this try is used cli/ in cli request object is inactive scope
            }
        }
    }
}