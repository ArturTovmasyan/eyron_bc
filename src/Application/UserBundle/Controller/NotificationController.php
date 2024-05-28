<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/27/16
 * Time: 6:56 PM
 */
namespace Application\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class NotificationController
 * @package Application\UserBundle\Controller
 *
 */
class NotificationController extends Controller
{
    /**
     * @Route("/notification-list", name="notification_list")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array();
    }
}