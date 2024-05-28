<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/20/16
 * Time: 11:39 AM
 */

namespace AppBundle\Services;

use AppBundle\Entity\Goal;
use AppBundle\Entity\UserGoal;
use Application\UserBundle\Entity\Badge;
use Application\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserGoalService extends AbstractProcessService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var
     */
    private $trans;

    /**
     * @var
     */
    private $liipImage;

    /**
     * @var
     */
    private $authorizationChecker;

    /**
     * @var UserGoalService $blService
     *
     */
    private $blService;

    /**
     * UserGoalService constructor.
     * @param EntityManager $em
     * @param $trans
     * @param $liipImage
     * @param $authorizationChecker
     * @param BucketListService $blService
     */
    public function __construct(EntityManager $em, $trans, $liipImage, $authorizationChecker, BucketListService $blService)
    {
        $this->em = $em;
        $this->trans = $trans;
        $this->liipImage = $liipImage;
        $this->authorizationChecker = $authorizationChecker;
        $this->blService = $blService;
    }

    /**
     * @param Request $request
     * @param Goal $goal
     * @param User $user
     * @return UserGoal|Response
     */
    public function addUserGoal(Request $request, Goal $goal, User $user)
    {
        $addBadge = 0;
        $removeBadge = 0;
        $persistUser = false;

        if($request->getContentType() == 'application/json' || $request->getContentType() == 'json'){
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        $userGoal = $this->em->getRepository("AppBundle:UserGoal")->findByUserAndGoal($user->getId(), $goal->getId());

        $suggestAsVisible = false;
        if (!$userGoal) {
            $userGoal = new UserGoal();
            $userGoal->setGoal($goal);
            $userGoal->setUser($user);
            $suggestAsVisible = true;
            $addBadge++;
        }

        if (!is_null($request->get('goal_status'))){

            $userGoalStatus = $userGoal->getStatus();
            $status = $request->get('goal_status');

            if($userGoalStatus == UserGoal::COMPLETED && $status == 0){
                $removeBadge ++;
            }
            elseif ($userGoalStatus == UserGoal::ACTIVE && $status == 1){
                $addBadge ++;
            }

            $userGoal->setStatus($request->get('goal_status') ? UserGoal::COMPLETED : UserGoal::ACTIVE);

            if($userGoal->getStatus() == UserGoal::COMPLETED)
            {
                 if (!$this->authorizationChecker->isGranted('done', $goal)) {
                     throw $this->createAccessDeniedException($this->trans->trans('goal.add_access_denied'));
                 }

                $completionDateRaw = $request->get('completion_date');
                if($completionDateRaw){
                    $completionDate = \DateTime::createFromFormat('d/m/Y', $completionDateRaw);

                    if(!$completionDate){
                        $completionDate = \DateTime::createFromFormat('m-d-Y', $completionDateRaw);
                    }

                    if(!$completionDate){
                        $completionDate = \DateTime::createFromFormat('Y-m-d', $completionDateRaw);
                    }

                    if(!$completionDate){
                        return new Response('Error completed date', Response::HTTP_BAD_REQUEST);
                    }
                }
                else {
                    $completionDate = new \DateTime();
                }

                $userGoal->setCompletionDate($completionDate);
            }
            elseif($userGoal->getStatus() == UserGoal::ACTIVE){
                $userGoal->setCompletionDate(null);
            }
        }

        if(!is_null($dateStatus = $request->get('date_status'))){
            $userGoal->setDateStatus($dateStatus);
        }

        if(!is_null($doDateStatus = $request->get('do_date_status'))){
            $userGoal->setDoDateStatus($doDateStatus);
        }

        if (!is_null($request->get('is_visible'))){
            $userGoal->setIsVisible($request->get('is_visible') ? true : false);
        }

        if (!is_null($steps = $request->get('steps')) && (is_array($steps) || is_array($steps = json_decode($steps)) )){
            $userGoal->setSteps($steps);
        }

        if (!is_null($request->get('note'))){
            $userGoal->setNote($request->get('note'));
        }

        if (!is_null($request->get('urgent'))){
            $userGoal->setUrgent($request->get('urgent') ? true : false);
            $user->setUserGoalRemoveDate(new \DateTime());  // change remove date to clear cache
            $persistUser = true;
        }

        if (!is_null($request->get('important'))){
            $userGoal->setImportant($request->get('important') ? true : false);
            $user->setUserGoalRemoveDate(new \DateTime()); // change remove date to clear cache
            $persistUser = true;
        }

//        $location = $request->get('location');
//        if(isset($location['address']) && isset($location['latitude']) && isset($location['longitude'])){
//            $userGoal->setAddress($location['address']);
//            $userGoal->setLat($location['latitude']);
//            $userGoal->setLng($location['longitude']);
//        }

        if($goal->isAuthor($user)  && $goal->getReadinessStatus() == Goal::DRAFT){
            // set status to publish
            $goal->setReadinessStatus(Goal::TO_PUBLISH);
            $this->em->persist($goal);
        }

        $doDateRaw = $request->get('do_date');
        if($doDateRaw){
            $doDate = \DateTime::createFromFormat('d/m/Y', $doDateRaw);

            if (!$doDate || $doDateRaw != $doDate->format('d/m/Y')){
                $doDate = \DateTime::createFromFormat('m/d/Y', $doDateRaw);
            }

            if(!$doDate){
                $doDate = \DateTime::createFromFormat('m-d-Y', $doDateRaw);
            }

            if(!$doDate){
                $doDate = \DateTime::createFromFormat('Y-m-d', $doDateRaw);
            }
            
            if(!$doDate){
                return new Response('Error do date', Response::HTTP_BAD_REQUEST);
            }

            if ($doDate->format('Y') < 100){
                $doDate->modify('+2000 year');
            }

            $userGoal->setDoDate($doDate);
        }

        if ($userGoal->getGoal()->getListPhotoDownloadLink()){
            $userGoal->getGoal()->setCachedImage($this->liipImage->getBrowserPath($userGoal->getGoal()->getListPhotoDownloadLink(), 'goal_list_big'));
        }

        $this->em->persist($userGoal);

        if($persistUser){
            $this->em->persist($user);
        }
        $this->em->flush();

        //set user activity value
        $this->blService->setUserActivity($user, $url);

        if ($suggestAsVisible){
            $userGoal->setIsVisible(true);
        }

        if($goal->getAuthor() && !$goal->getAuthor()->isAdmin() && $goal->getPublish() == Goal::PUBLISH){
            // check badge
            if($addBadge > 0){

                // add score for innovator
                $this->runAsProcess('bl.badge.service', 'addScore',
                    array(Badge::TYPE_INNOVATOR, $goal->getAuthor()->getId(), $addBadge));
                // check badge
            }elseif($removeBadge > 0){

                // add score for innovator
                $this->runAsProcess('bl.badge.service', 'removeScore',
                    array(Badge::TYPE_INNOVATOR, $goal->getAuthor()->getId(), $removeBadge));
            }
        }

        return $userGoal;
    }

    /**
     * @param Goal $goal
     * @param User $user
     * @param $isDone
     * @return bool
     */
    public function doneBy(Goal $goal, User $user, $isDone)
    {
        $score = 0;

        if($isDone){
            $status = UserGoal::COMPLETED;
            $completionDate = new \DateTime('now');
            $score ++;
        }
        else {
            $status = UserGoal::ACTIVE;
            $completionDate = null;
        }

        $newDone = true;
        $userGoal = $this->em->getRepository("AppBundle:UserGoal")->findByUserAndGoal($user->getId(), $goal->getId());

        if(!$userGoal){
            $userGoal = new UserGoal();
            $userGoal->setGoal($goal);
            $userGoal->setUser($user);
            $userGoal->setIsVisible(true);
            $score ++;
        }
        else {
            $newDone = !($userGoal->getStatus() == UserGoal::COMPLETED);
        }

        $userGoal->setStatus($status);
        $userGoal->setCompletionDate($completionDate);


        $this->em->persist($userGoal);
        $this->em->flush();

        //set user activity value
        $this->blService->setUserActivity($user, $url);

        // check if status is completed, and author is not admin
        if($isDone && $goal->getAuthor() && !$goal->getAuthor()->isAdmin()){

            // add score for innovator
            $this->runAsProcess('bl.badge.service', 'addScore',
                array(Badge::TYPE_INNOVATOR, $goal->getAuthor()->getId(), $score));

        }

        return $newDone;

    }

    /**
     * @param $userGoalId
     * @param User $user
     * @return mixed
     */
    public function deleteUserGoal($userGoalId, User $user)
    {
        $userGoal = $this->em->getRepository("AppBundle:UserGoal")->find($userGoalId);

        // get goal author
        $author = $userGoal->getGoal()->getAuthor();

        $msg = $this->em->getRepository('AppBundle:UserGoal')->removeUserGoal($user->getId(), $userGoal);

        // check if status is completed, and author is not admin
        if(is_numeric($msg) && $author && !$author->isAdmin()){

            $score = $userGoal->getStatus() == UserGoal::COMPLETED ? 2 : 1;

            $score = $msg == UserGoal::DELETE ? $score + 1 : $score;

            // add score for innovator
            $this->runAsProcess('bl.badge.service', 'removeScore',
                array(Badge::TYPE_INNOVATOR, $author->getId(), $score));
        }

        return $msg;

    }

}