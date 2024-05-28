<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/26/15
 * Time: 5:34 PM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\UserGoal;
use Application\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * Class BucketListController
 * @package AppBundle\Controller
 */
class BucketListController extends Controller
{
    /**
     * @Route("/profile/{status}", defaults={"status" = null}, name="user_profile_single", requirements={"status"="active-goals|completed-goals|all"})
     * @Route("/profile/{user}/{status}", defaults={"status" = null}, requirements={"status"="active-goals|completed-goals|all|", "user"="[A-Za-z0-9]+"}, name="user_profile")
     * @Template()
     * @param User $user
     * @return array
     * @Secure(roles="ROLE_USER")
     */
    public function myListAction(Request $request, $user = null)
    {
        $this->container->get('bl.doctrine.listener')->disableIsMyGoalLoading();
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();

        $em = $this->getDoctrine()->getManager();

        if($user){
            $user = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('uId' => $user));
        }
        else {
            $user = $this->getUser();
        }

        if(!$user->isEnabled()) {
            return $this->redirectToRoute('user_profile_no_active');
        }

        $em->getRepository('ApplicationUserBundle:User')->setUserStats($user);

        // create filter
        $filters = array(
            UserGoal::URGENT_IMPORTANT         => 'filter.import_urgent',
            UserGoal::URGENT_NOT_IMPORTANT     => 'filter.not_import_urgent',
            UserGoal::NOT_URGENT_IMPORTANT     => 'filter.import_not_urgent',
            UserGoal::NOT_URGENT_NOT_IMPORTANT => 'filter.not_import_not_urgent',
        );

        //This part is used for profile completion percent calculation
        if ($this->getUser()->getProfileCompletedPercent() != 100) {
            $em->getRepository("ApplicationUserBundle:User")->updatePercentStatuses($this->getUser());
        }

        // get drafts
        $myIdeasCount =  $em->getRepository("AppBundle:Goal")->findMyIdeasCount($user);

        return array(
            'profileUser'  => $user,
            'myIdeasCount' => $myIdeasCount,
            'filters'      => $filters
        );
    }

    /**
     * @Route("/no-active", name="user_profile_no_active")
     * @Template()
     * @return array
     */
    public function noActiveAction()
    {
        return array();
    }
}