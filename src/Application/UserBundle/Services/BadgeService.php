<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/17/16
 * Time: 1:34 PM
 */

namespace Application\UserBundle\Services;
use AppBundle\Services\AbstractProcessService;
use AppBundle\Services\ApcService;
use AppBundle\Services\PutNotificationService;
use AppBundle\Services\UserNotifyService;
use Application\UserBundle\Entity\Badge;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Process;

/**
 * Class BadgeService
 * @package Application\UserBundle\Services
 */
class BadgeService extends AbstractProcessService
{
    const BADGE_MAX_SCORE = 'badge_max_score';
    const TOP_BADGES_USERS = 'top_badges_users';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UserNotifyService
     */
    private $notifyService;

    /**
     * @var PutNotificationService
     */
    private $pushNote;

    /**
     * @var NotificationService
     */
    private $notification;

    /**
     * @var
     */
    private $router;
    
    /**
     * @var
     */
    private $liipManager;

    /**
     * @var
     */
    private $apc;

    /**
     * BadgeService constructor.
     * @param EntityManager $em
     * @param UserNotifyService $notifyService
     * @param PutNotificationService $pushNote
     * @param NotificationService $notification
     * @param $router
     * @param $liipManager
     */
    public function __construct(EntityManager $em, UserNotifyService $notifyService,
                                PutNotificationService $pushNote,
                                NotificationService $notification,
                                $router, $liipManager, ApcService $apc)
    {
        $this->em               = $em;
        $this->notifyService    = $notifyService;
        $this->pushNote         = $pushNote;
        $this->notification     = $notification;
        $this->router           = $router;
        $this->liipManager      = $liipManager;
        $this->apc              = $apc;
    }

    /**
     * @return array
     */
    public function findTopUsers()
    {
        $count = 10;
        $minInnovatorScore = 0;
        $minMotivatorScore = 0;
        $minTravellerScore = 0;
        $userIds = [];
        $minUpdate = null;

        // get repo
        $repo = $this->em->getRepository("ApplicationUserBundle:Badge");

        // get innovators
        $innovators = $repo->findTopUsersIdByType(Badge::TYPE_INNOVATOR, $count);

        // check innovators
        if(is_array($innovators) && count($innovators) > 0){
            $maxScoreBadge = reset($innovators);
            $maxScore = $maxScoreBadge->getScore();

            // map for values and normalize scores
            array_map(function($badge) use ($maxScore, &$userIds, &$minUpdate){
                $normalizedScore = $badge->getScore()/$maxScore * Badge::MAXIMUM_NORMALIZE_SCORE;
                $normalizedScore = ceil($normalizedScore);
                $badge->normalizedScore = $normalizedScore;
                $userIds[] = $badge->getUser()->getId();

                $user = $badge->getUser();
                if($user->getImagePath()){
                    $user->setCachedImage($this->liipManager->getBrowserPath($user->getImagePath(), 'user_icon'));
                }

                $update = $badge->getUpdated();
                $minUpdate = $update > $minUpdate ? $update :  $minUpdate;



            }, $innovators);

            $minInnovator = end($innovators);
            $minInnovatorScore = $minInnovator->normalizedScore;
        }

        // get motivators
        $motivators = $repo->findTopUsersIdByType(Badge::TYPE_MOTIVATOR, $count);

        // check motivator
        if(is_array($motivators) && count($motivators) > 0){
            $maxScoreBadge = reset($motivators);
            $maxScore = $maxScoreBadge->getScore();

            // map for values and normalize scores
            array_map(function($badge) use ($maxScore, &$userIds, &$minUpdate){
                $normalizedScore = $badge->getScore()/$maxScore * Badge::MAXIMUM_NORMALIZE_SCORE;
                $normalizedScore = ceil($normalizedScore);
                $badge->normalizedScore = $normalizedScore;
                $userIds[] = $badge->getUser()->getId();

                $user = $badge->getUser();
                if($user->getImagePath()){
                    $user->setCachedImage($this->liipManager->getBrowserPath($user->getImagePath(), 'user_icon'));
                }

                $update = $badge->getUpdated();
                $minUpdate = $update > $minUpdate ? $update :  $minUpdate;

            }, $motivators);

            $minMotivator = end($motivators);
            $minMotivatorScore = $minMotivator->normalizedScore;
        }

        // get travellers
        $travellers = $repo->findTopUsersIdByType(Badge::TYPE_TRAVELLER, $count);

        // check motivator
        if(is_array($travellers) && count($travellers) > 0){
            $maxScoreBadge = reset($travellers);
            $maxScore = $maxScoreBadge->getScore();


            // map for values and normalize scores
            array_map(function($badge) use ($maxScore, &$userIds, &$minUpdate){
                $normalizedScore = $badge->getScore()/$maxScore * Badge::MAXIMUM_NORMALIZE_SCORE;
                $normalizedScore = ceil($normalizedScore);
                $badge->normalizedScore = $normalizedScore;
                $userIds[] = $badge->getUser()->getId();

                $user = $badge->getUser();
                if($user->getImagePath()){
                    $user->setCachedImage($this->liipManager->getBrowserPath($user->getImagePath(), 'user_icon'));
                }

                $update = $badge->getUpdated();
                $minUpdate =  $update > $minUpdate ? $update :  $minUpdate;

            }, $travellers);

            $minTraveller = end($travellers);
            $minTravellerScore = $minTraveller->normalizedScore;
        }

        // unique ids
        $userIds = array_unique($userIds);

        // generate result
        $result = array(
            'min' => array(
                'innovator' => $minInnovatorScore,
                'motivator' => $minMotivatorScore,
                'traveller' => $minTravellerScore,
            ),
            'badges' => array(
                'innovator' => $innovators,
                'motivator' => $motivators,
                'traveller' => $travellers,
            ),
            'users' => $userIds,
            'maxUpdate' => $minUpdate
        );

        $this->apc->apc_store(self::TOP_BADGES_USERS, $result);

        return $result;
    }

    /**
     * This function is used to find badge by user, and add score
     *
     * @param $type
     * @param $userId
     * @param $score
     * @param bool $notify
     * @throws \Exception
     */
    public function addScore($type, $userId, $score, $notify = true)
    {
        // get user
        $user = $this->em->getRepository("ApplicationUserBundle:User")->find($userId);

        if(!$user){
            throw new NotFoundHttpException('User not found');
        }
        $this->em->getConnection()->beginTransaction(); // suspend auto-commit
        // get badge
        $badge = $this->em->getRepository("ApplicationUserBundle:Badge")
            ->findBadgeByUserAndType($userId, $type);

        try {

            if(!$badge){

                $badge = new Badge();
                $badge->setType($type);
                $badge->setUser($user);
            }

            $oldScore = $badge->getScore();

            // generate new score
            $newScore = $oldScore + $score;

            $badge->setScore($newScore);
            
            if($newScore > 0){
                $user->setLevel($type, true);
            } else {
                $user->setLevel($type, false);
            }
            
            $this->em->persist($badge);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }


        // get max score from cache
        $maxScore = $this->getMaxScore($newScore, $type);

        // get score by type
        $typMaxScore = $maxScore[$type];

        // check is new score bigger
        if($newScore > $typMaxScore){

            // generate new max score
            $maxScore[$type] = $newScore;

            // add to cache
            $this->apc->apc_delete(self::BADGE_MAX_SCORE);
            $this->apc->apc_store(self::BADGE_MAX_SCORE, $maxScore);
        }

        // check has changed
        if($notify && $this->hasScoreChanged($newScore, $oldScore, $type)){

            $this->runAsProcess('bl.badge.service', 'sendNotify',
                array($userId, 1, $type));
        }

    }

    /**
     * @param $newScore
     * @param $oldScore
     * @param $type
     * @return bool
     */
    private function hasScoreChanged($newScore, $oldScore, $type)
    {
        // get score
        $scores = $this->getMaxScore();

        // get type
        $maxScore = $scores[$type];

        // new score
        $newNormalizedScore = ceil($newScore / $maxScore * Badge::MAXIMUM_NORMALIZE_SCORE);

        // old score
        $oldNormalizedScore = ceil($oldScore / $maxScore * Badge::MAXIMUM_NORMALIZE_SCORE);

        if($newNormalizedScore != $oldNormalizedScore){
            return true;
        }

        return false;
    }

    /**
     * @param $userId
     * @param $increase
     * @param $type
     */
    public function sendNotify($userId, $increase, $type)
    {
        // get user
        $user = $this->em->getRepository("ApplicationUserBundle:User")->find($userId);

        if(!$user){
            throw new NotFoundHttpException('User not found');
        }
        $typeAsString = $type == Badge::TYPE_INNOVATOR ? 'an ' : 'a ';
        $types = Badge::getTypesAsString();
        $typeAsString .= array_key_exists($type, $types) ? $types[$type] : '';

        if($increase){
            $message = "Congratulations! You rose to the top on the leaderboard as $typeAsString.";

        }else{
            $message = "Oops! You went down on the leaderboard as an $typeAsString . To reach the top, devote more time to Bucket List..";
        }

        $link = $this->router->generate('leaderboard') . ($type == Badge::TYPE_MOTIVATOR ? '/mentor' : '');
        $this->notification->sendNotification(null, $link, null, $message, $user);
//        $this->notifyService->sendEmail($user->getEmail(), $message, 'increase-decrease on the leaderboard');
        $this->pushNote->sendPushNote($user, $message);

    }


    /**
     * This function is used to find badge by user, and remove score
     *
     * @param $type
     * @param $userId
     * @param $score
     * @param $notify
     * @throws \Exception
     */
    public function removeScore($type, $userId, $score, $notify = true)
    {
        // get user
        $user = $this->em->getRepository("ApplicationUserBundle:User")->find($userId);

        if(!$user){
            throw new NotFoundHttpException('User not found');
        }
        $this->em->getConnection()->beginTransaction(); // suspend auto-commit

        // get badge
        $badge = $this->em->getRepository("ApplicationUserBundle:Badge")
            ->findBadgeByUserAndType($userId, $type);

        if($badge){

            try {
                $oldScore = $badge->getScore();
                // generate new score
                $newScore = $oldScore - $score;
                $newScore = $newScore < 0 ? 0 : $newScore;

                if($newScore == 0){
                    $this->em->remove($badge);
                    $user->setLevel($type, false);
                }else{
                    $badge->setScore($newScore);
                    $this->em->persist($badge);
                    $user->setLevel($type, true);
                }

                $this->em->flush();
                $this->em->getConnection()->commit();
            } catch (\Exception $e) {
                $this->em->getConnection()->rollBack();
                throw $e;
            }



            // get max score from cache
            $maxScore = $this->getMaxScore($newScore, $type);

            // get score by type
            $typMaxScore = $maxScore[$type];

            // check is new score bigger
            if($newScore === $typMaxScore){

                // generate new max score
                $maxScore[$type] = $newScore;

                // add to cache
                $this->apc->apc_delete(self::BADGE_MAX_SCORE);
                $this->apc->apc_store(self::BADGE_MAX_SCORE, $maxScore);
            }

            // check has changed
            if($notify && $this->hasScoreChanged($newScore, $oldScore, $type)){

                $this->runAsProcess('bl.badge.service', 'sendNotify',
                    array($userId, 0, $type));
            }
        }
    }

    /**
     * @param int $score
     * @param int $type
     * @return mixed
     */
    public function getMaxScore($score = 0, $type = 0)
    {
        $badgeMaxScore = $this->apc->apc_fetch(self::BADGE_MAX_SCORE);

        $getNewFromDb = true;
        if(is_array($badgeMaxScore) &&
            array_key_exists($type, $badgeMaxScore) &&
            $badgeMaxScore[$type] >= $score){
            $getNewFromDb = false;
        }

        if(!$badgeMaxScore || $getNewFromDb){

            $badgeMaxScore = $this->em->getRepository('ApplicationUserBundle:Badge')->getMaxScores();
            $this->apc->apc_store(self::BADGE_MAX_SCORE, $badgeMaxScore);
        }

        return $badgeMaxScore;
    }
}