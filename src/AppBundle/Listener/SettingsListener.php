<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/6/15
 * Time: 2:59 PM
 */

namespace AppBundle\Listener;

use Application\UserBundle\Entity\User;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\PreFlush;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SettingsListener implements ContainerAwareInterface
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
     * @param User $user
     * @param PreFlushEventArgs $event
     * @PreFlush()
     */
    public function preFlush(User $user, PreFlushEventArgs $event)
    {
        //get stopwatch component
        $stopwatch = $this->container->get('debug.stopwatch');

        // Start event named 'eventName'
        $stopwatch->start('bl_pre_flush_event_for_settings');

        //get add email
        $addEmail = $user->addEmail;

        //check if addEmail exist
        if ($addEmail) {

            //get user emails
            $userEmails = $user->getUserEmails();

            //generate email activation  token
            $emailToken = md5(microtime() . $addEmail);

            //set user emails in array with token and primary value
            $newEmail = ['userEmails' => $addEmail, 'token' => $emailToken, 'primary' => false];

            //set new email data in userEmails array
            $userEmails[$addEmail] = $newEmail;

            //get 8user full name
            $userName = $user->showName();

            //get send activation email service
            $this->container->get('bl.email.sender')->sendActivationUserEmail($addEmail, $emailToken, $userName);

            //set user emails
            $user->setUserEmails($userEmails);
        }

        // Start event named 'eventName'
        $stopwatch->stop('bl_pre_flush_event_for_settings');
    }
}

