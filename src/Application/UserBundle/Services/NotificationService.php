<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/27/16
 * Time: 6:23 PM
 */
namespace Application\UserBundle\Services;

use Application\UserBundle\Entity\Notification;
use Application\UserBundle\Entity\User;
use Application\UserBundle\Entity\UserNotification;
use Doctrine\ORM\EntityManager;

class NotificationService
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function sendNotification($user, $link, $goalId, $body, $toUsers)
    {
        if (!is_array($toUsers)){
            $toUsers = [$toUsers];
        }

        if (count($toUsers) == 0){
            return null;
        }

        $notification = new Notification();
        $notification->setLink($link);
        $notification->setGoalId($goalId);
        $notification->setBody($body);
        $notification->setPerformer($user);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $insertDataInQuery = '';
        foreach($toUsers as $toUser){
            if (!is_null($user) && $user->getId() == $toUser->getId()){
                continue;
            }

            $currentDate = new \DateTime();
            $insertDataInQuery .= "(false, {$toUser->getId()}, {$notification->getId()}, '{$currentDate->format('Y-m-d H:i:s')}', '{$currentDate->format('Y-m-d H:i:s')}'),";
        }

        if ($insertDataInQuery){
            $insertDataInQuery = 'INSERT INTO user_notification (is_read, user_id, notification_id, updated, created) VALUES ' . trim($insertDataInQuery, ',');

            $stmt = $this->entityManager->getConnection()->prepare($insertDataInQuery);
            $stmt->execute();
        }


        return null;
    }
}