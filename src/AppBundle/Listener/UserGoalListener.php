<?php

namespace AppBundle\Listener;

use AppBundle\Entity\UserGoal;
use Application\UserBundle\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\PreFlush;
use Doctrine\ORM\Mapping\PreRemove;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserGoalListener implements ContainerAwareInterface
{
    /**
     * @var
     */
    public $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param UserGoal $userGoal
     * @param PreFlushEventArgs $event
     * @PreFlush()
     */
    public function preFlush(UserGoal $userGoal, PreFlushEventArgs $event)
    {
        //get stopwatch component
        $stopwatch = $this->container->get('debug.stopwatch');

        // Start event named 'eventName'
        $stopwatch->start('bl_pre_flush_event_for_userGoal');

        $this->removeQueryCache($userGoal);

        // Start event named 'eventName'
        $stopwatch->stop('bl_pre_flush_event_for_userGoal');
    }

    /**
     * @param UserGoal $userGoal
     * @param LifecycleEventArgs $event
     * @PreRemove()
     */
    public function preRemove(UserGoal $userGoal, LifecycleEventArgs $event)
    {
        //get stopwatch component
        $stopwatch = $this->container->get('debug.stopwatch');

        // Start event named 'eventName'
        $stopwatch->start('bl_pre_remove_event_for_userGoal');

        $this->removeQueryCache($userGoal);

        // Start event named 'eventName'
        $stopwatch->stop('bl_pre_remove_event_for_userGoal');
    }

    /**
     * @param $userGoal
     */
    private function removeQueryCache($userGoal)
    {
        $this->container
            ->get('doctrine')
            ->getManager()
            ->getConfiguration()
            ->getResultCacheImpl()
            ->delete('user_goal_' . $userGoal->getUser()->getId());
    }
}

