<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/20/16
 * Time: 3:01 PM
 */

namespace AppBundle\Services;
use Application\UserBundle\Entity\Badge;
use Application\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GoalService
 * @package AppBundle\Services
 */
class GoalService extends AbstractProcessService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var
     */
    private $cacheManager;

    /**
     * @var
     */
    private $serializer;


    /**
     * GoalService constructor.
     * @param EntityManager $em
     * @param $cacheManager
     * @param $serializer
     */
    public function __construct(EntityManager $em, $cacheManager, $serializer)
    {
        $this->em = $em;
        $this->cacheManager = $cacheManager;
        $this->serializer = $serializer;
    }


    /**
     * @param $service
     * @param $function
     * @param array $arguments
     */
    public function addBadgeForPublish($service, $function, array $arguments)
    {
        // add score for innovator
        $this->runAsProcess($service, $function, $arguments);

    }

    public function getOwnedData(Request $request, User $user, $isMy,  $first, $count)
    {
        $response = new Response();

        $function = $isMy ? 'getOwnedUserGoals' : 'getOwnedGoals';

        $arguments = array($user, $first, $count);
//        $lastUpdateArg = array_merge($arguments, [true]);
//        $lastUpdated =  call_user_func_array(array($this, $function), $lastUpdateArg);
//
//        // get last updated
//        if (is_null($lastUpdated)) {
//            return  ['goals' => [] ];
//        }
//        $response->setLastModified($lastUpdated);
//        $response->headers->set('cache-control', 'private, must-revalidate');
//
//        // check is modified
//        if ($response->isNotModified($request)) {
//            return $response;
//        }

        $data =  call_user_func_array(array($this, $function), $arguments);
        $result = ['goals' => $data];
        $serializedContent = $this->serializer->serialize($result, 'json',
            SerializationContext::create()->setGroups(array("userGoal", "userGoal_goal", "tiny_goal", "goal_author", "tiny_user")));
        $response->setContent($serializedContent);

        return $response;

    }


    /**
     * @param User $user
     * @param $first
     * @param $count
     * @param bool $getLastUpdated
     * @return array
     */
    public function getOwnedGoals(User $user, $first, $count, $getLastUpdated = false)
    {
        $publish = true;

        //get entity manager
        $em = $this->em;

        if($getLastUpdated){

            // get last updated
            $lastUpdated = $em->getRepository('AppBundle:Goal')->findOwnedGoals($user->getId(), $first, $count,
                $publish, true);

            return $lastUpdated;
        }



        // get owned goals
        $goals = $em->getRepository('AppBundle:Goal')->findOwnedGoals($user->getId(), $first, $count, $publish);

        $liipManager = $this->cacheManager;

        //This part is used to calculate goal stats
        $goalIds   = [];
        $authorIds = [];

        foreach ($goals as $goal){
            $goalIds[$goal->getId()] = 1;
            if ($goal->getAuthor()) {
                $authorIds[] = $goal->getAuthor()->getId();
            }

            if ($goal->getListPhotoDownloadLink()) {
                try {
                    $goal->setCachedImage($liipManager->getBrowserPath($goal->getListPhotoDownloadLink(),
                        !$publish ? 'goal_bucketlist' : 'goal_list_horizontal'));
                } catch (\Exception $e) {
                    $goal->setCachedImage("");
                }
            }
        }


        $goalStats = $em->getRepository("AppBundle:Goal")->findGoalStateCount($goalIds, true);
        $authorstats = $em->getRepository("ApplicationUserBundle:User")->findUsersStats($authorIds);

        foreach($goals as $goal){
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

        $em->getRepository('ApplicationUserBundle:User')->setUserStats($user);

        return $goals;
    }

    /**
     * @param User $user
     * @param $first
     * @param $count
     * @param bool $getLastUpdated
     * @return array
     */
    public function getOwnedUserGoals(User $user, $first, $count,  $getLastUpdated = false)
    {
        $publish = false;
        //get entity manager
        $em = $this->em;

        if($getLastUpdated){

            // get last updated
            $lastUpdated = $em->getRepository('AppBundle:Goal')->findOwnedGoals($user->getId(), $first, $count,
                $publish, true);

            return $lastUpdated;
        }

        // get owned goals
        $ownedGoals = $em->getRepository('AppBundle:Goal')->findOwnedGoals($user->getId(), $first, $count, $publish);
        $liipManager = $this->cacheManager;
        //This part is used to calculate goal stats
        $goalIds   = [];
        $authorIds = [];

        foreach($ownedGoals as $userGoal){
            $goalIds[$userGoal->getGoal()->getId()] = 1;
            if ($userGoal->getGoal()->getAuthor()) {
                $authorIds[] = $userGoal->getGoal()->getAuthor()->getId();
            }

            $goal = $userGoal->getGoal();
            if ($goal->getListPhotoDownloadLink()) {
                try {
                    $goal->setCachedImage($liipManager->getBrowserPath($goal->getListPhotoDownloadLink(),
                        !$publish ? 'goal_bucketlist' : 'goal_list_horizontal'));
                } catch (\Exception $e) {
                    $goal->setCachedImage("");
                }
            }

        }

        $goalStats = $em->getRepository("AppBundle:Goal")->findGoalStateCount($goalIds, true);
        $authorstats = $em->getRepository("ApplicationUserBundle:User")->findUsersStats($authorIds);

        foreach($ownedGoals as $userGoal){
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

        return $ownedGoals;


    }

    /**
     * @param User $user
     * @return mixed
     */
    public function getUnConfirmGoals(User $user)
    {
        $goals = $this->em->getRepository("AppBundle:Goal")->findUserUnConfirmInPlace($user);

        //
        $addBadge = 0;

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

                        $places = $goal->getPlace()->toArray();

                        array_map(function($place) use (&$addBadge){
                            $type = $place->getPlaceType(); // get type
                            $typeName = $type->getName(); // get name

                            if($typeName == 'country'){
                                $addBadge+= 5;

                            }elseif ($typeName == 'city'){
                                $addBadge+= 2;

                            }else{
                                $addBadge+= 1;
                            }

                        }, $places);

                        //set confirm value
                        $confirm = true;

                        $this->em->persist($userGoal);
                    }
                }
            }
            //check if user has confirmed goal
            if ($confirm) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        if($addBadge > 0){

            // add score for innovator
            $this->addBadgeForPublish('bl.badge.service', 'addScore',
                array(Badge::TYPE_TRAVELLER, $user->getId(), $addBadge));
        }

        return $goals;
    }

}
