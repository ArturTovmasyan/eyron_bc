<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Goal;
use AppBundle\Entity\Page;
use AppBundle\Entity\UserGoal;
use AppBundle\Services\UserNotifyService;
use Application\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AppBundle\Form\ContactUsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class MainController
 * @package AppBundle\Controller
 */
class MainController extends Controller
{
    /**
     * This action is used to redirect all gone pages
     * @Route("/listed-users/{slug}")
     * @Route("/done-users/{slug}")
     */
    public function gonePagesAction()
    {
        throw new HttpException(Response::HTTP_GONE);   
    }
    
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if (!is_object($user)){

            $response = new Response();
            $response->setPublic();
            $response->headers->set('Cache-Control', 'public, must-revalidate');

            $currentDate = new \DateTime();
            $currentDate->setTimezone(new \DateTimeZone('UTC'));
            $currentDate->setTime(0, 0, 0);

            $response->setLastModified($currentDate);

            $response->headers->set('cache-control', 'private, must-revalidate');

            if ($response->isNotModified($request)){
                return $response;
            }

            $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();

            $goals = $em->getRepository("AppBundle:Goal")->findPopular(7);
            $em->getRepository("AppBundle:Goal")->findGoalStateCount($goals);

            $stories = $em->getRepository("AppBundle:SuccessStory")->findInspireStories();

            $goalIds = [];
            foreach($stories as $story){
                $goalIds[$story->getGoal()->getId()] = 1;
            }

            $stats = $em->getRepository("AppBundle:Goal")->findGoalStateCount($goalIds, true);
            foreach($stories as &$story){
                $story->getGoal()->setStats([
                    'listedBy' => $stats[$story->getGoal()->getId()]['listedBy'],
                    'doneBy'   => $stats[$story->getGoal()->getId()]['doneBy'],
                ]);
            }

            return $this->render('AppBundle:Main:index.html.twig', array('goals' => $goals, 'stories' => $stories), $response);
        }


        if ($request->getSession()->has('vote_story_id')){
            $storyId = $request->getSession()->get('vote_story_id');
            $request->getSession()->remove('vote_story_id');
            $story = $em->getRepository('AppBundle:SuccessStory')->find($storyId);
            $this->get('bl_story_service')->voteStory($storyId, $this->getUser());

            return $this->redirectToRoute('inner_goal', ['slug' => $story->getGoal()->getSlug()]);
        }

        //check and set user activity by new feed count
        $url = 'goals_list';
        $this->get('bl_service')->setUserActivity($user, $url);
        
        return $this->redirectToRoute($url);
    }

    /**
     * @Route("/page/{slug}", name="page")
     * @Template()
     *
     * @param Request $request
     * @param $slug
     * @return array|Response
     */
    public function pageAction(Request $request, $slug)
    {
        $em   = $this->getDoctrine()->getManager();
        $env  = $this->get('kernel')->getEnvironment();
        $page = $em->getRepository("AppBundle:Page")->findOneBy(array('slug' => $slug));

        if(!$page){
            throw $this->createNotFoundException("Page has not been found!");
        }

        if($slug == 'contact-us'){
            $form = $this->createForm(ContactUsType::class);

            if($request->isMethod("POST")){
                $form->handleRequest($request);

                if($form->isValid()){
                    $formData = $form->getData();
                    $admins = $em->getRepository('ApplicationUserBundle:User')->findAdmins('ROLE_SUPER_ADMIN');

                    foreach($admins as $admin){
                        $this->get('bl.email.sender')->sendContactUsEmail($admin['email'], $admin['fullName'], $formData);
                    }

                    return ['page' => $page, 'isSend' => true];
                }
            }

            return ['page' => $page, 'isSend' => false, 'form' => $form->createView()];
        }


        $response = new Response();

        if($env == 'prod')
        {
            // set last modified data
            $response->setLastModified($page->getUpdated());
            // Set response as public. Otherwise it will be private by default.
            $response->setPublic();

            // Check that the Response is not modified for the given Request
            if ($response->isNotModified($request)) {
                // return the 304 Response immediately
                return $response;
            }
        }

        return $this->render('AppBundle:Main:page.html.twig', array('page' => $page), $response);
    }

    /**
     * @Route("/leaderboard", name="leaderboard")
     * @Template()
     */
    public function leaderboardAction()
    {
        return [];
    }

    /**
     * This action is used to include user block in header
     * @Template()
     * @return array
     */
    public function esiMenuAction()
    {
        return array();
    }

    /**
     * This action is used to include user block in header
     * @Route("/esi-user-for-amp", name="esi_user_for_amp")
     * @Template()
     * @return array
     */
    public function esiUserForAmpAction()
    {
        $user = $this->getUser();

        return array('user' => $user);
    }


    /**
     * @Route("/goal-friends", name="goal_friends")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @return array
     */
    public function goalFriendsAction(Request $request)
    {
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();
        $em          = $this->getDoctrine()->getManager();
        $em->getRepository('ApplicationUserBundle:User')->setUserStats($this->getUser());

        //This part is used for profile completion percent calculation
        if ($this->getUser()->getProfileCompletedPercent() != 100) {
            $em->getRepository("ApplicationUserBundle:User")->updatePercentStatuses($this->getUser());
        }
        return array();
    }

    /**
     * @Route("/notifications", name="notifications")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @return array
     */
    public function notificationsAction(Request $request)
    {
        return array();
    }

    /**
     * @Route("/activity", name="activity")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @return array
     */
    public function activitiesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('ApplicationUserBundle:User')->setUserStats($this->getUser());

        //This part is used for profile completion percent calculation
        if ($this->getUser()->getProfileCompletedPercent() != 100) {
            $em->getRepository("ApplicationUserBundle:User")->updatePercentStatuses($this->getUser());
        }

        return array();
    }

    /**
     * @Route("/register/confirmed", name="registration_confirmed")
     * @Security("has_role('ROLE_USER')")
     * @return array
     */
    public function registrationConfirmedAction()
    {
        return $this->redirectToRoute('activity');
    }

    /**
     * @param $index
     * @param $array
     * @return bool
     */
    private function checkAndGetFromArray($index, &$array)
    {
        // check data
        foreach ($array as $key => $value){
            if($index == $value['dates']){
                $result = $value['counts'];
                unset($array[$key]);
                return $result;
            }
        }
        return 0;
    }

    /**
     * This function is view all statistic
     *
     * @Route("/admin/statistic", name="statistic")
     * @Template()
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_GOD')")
     */
    public function allStatisticAction()
    {
        return [];
    }
}


