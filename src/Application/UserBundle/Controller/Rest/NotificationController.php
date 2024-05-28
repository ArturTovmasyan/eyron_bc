<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/27/16
 * Time: 6:56 PM
 */
namespace Application\UserBundle\Controller\Rest;

use Application\UserBundle\Entity\UserNotification;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class NotificationController
 * @package Application\UserBundle\Controller
 *
 * @Rest\RouteResource("Notification")
 * @Rest\Prefix("/api/v1.0")
 */
class NotificationController extends Controller
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Notification",
     *  description="This function is used to get user notifications, if you send negative lastId then return notifications which id is greater then abs(lastId), if you send positive lastId then return notifications which id is less then lastId ",
     *  statusCodes={
     *         200="Returned when notifications was returned",
     *         401="Returned when user not authenticated"
     *  },
     *
     * )
     *
     * @Rest\View(serializerGroups={"userNotification", "userNotification_notification", "notification", "notification_performer", "tiny_user"})
     * @Rest\Get("/notifications/{first}/{count}/{lastId}", defaults={"lastId"=null}, requirements={"first"="\d+", "count"="\d+", "lastId"="-{0,1}\d+"}, name="get_notification", options={"method_prefix"=false})
     * @Secure(roles="ROLE_USER")
     *
     * @param $request
     * @param $first
     * @param $count
     * @param $lastId
     * @return array
     */
    public function getAction(Request $request, $first, $count, $lastId = null)
    {
        $em = $this->getDoctrine()->getManager();

        $lastModified = $em->getRepository('ApplicationUserBundle:UserNotification')
            ->getUserNotifications($this->getUser()->getId(), $first, $count, $lastId, true);
//        if (is_null($lastId)){
            $unreadCount = $em->getRepository('ApplicationUserBundle:UserNotification')
                ->getUnreadCount($this->getUser()->getId());
//        }

        if (is_null($lastModified)){
            return [
                'userNotifications' => [],
                'unreadCount'       => isset($unreadCount) ? $unreadCount : null
            ];
        }

        $response = new Response();
        $response->setLastModified($lastModified['lastModified']);
        $response->headers->set('cache-control', 'private, must-revalidate');
        $response->headers->set('ETag', $lastModified['etag']);

        if ($response->isNotModified($request)){
            return $response;
        }

        $userNotifications = $em->getRepository('ApplicationUserBundle:UserNotification')
            ->getUserNotifications($this->getUser()->getId(), $first, $count, $lastId);


        $content = [
            'userNotifications' => $userNotifications,
            'unreadCount'       => isset($unreadCount) ? $unreadCount : null
        ];

        $serializer = $this->get('serializer');
        $serializedContent = $serializer->serialize($content, 'json',
            SerializationContext::create()->setGroups(array("userNotification", "userNotification_notification", "notification", "notification_performer", "tiny_user")));

        $response->setContent($serializedContent);
        return $response;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Notification",
     *  description="This function is used to set all notifications as read",
     *  statusCodes={
     *         200="Returned when notifications was set as unread",
     *         401="Returned when user not authenticated"
     *  },
     *
     * )
     *
     * @Secure(roles="ROLE_USER")
     * @return array
     */
    public function getAllReadAction()
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('ApplicationUserBundle:UserNotification')->setAsReadAllNotifications($this->getUser()->getId());

        return new Response('ok');
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Notification",
     *  description="This function is used to set single notification as read",
     *  statusCodes={
     *         200="Returned when notification was set as unread",
     *         401="Returned when user not authenticated",
     *         404="Returned when userNotification not found with that id",
     *  },
     *
     * )
     *
     * @Secure(roles="ROLE_USER")
     * @param UserNotification $userNotification
     * @return Response
     */
    public function getReadAction(UserNotification $userNotification)
    {
        $em = $this->getDoctrine()->getManager();
        $userNotification->setIsRead(true);
        $em->flush();

        return new Response('ok');
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Notification",
     *  description="This function is used to remove user notification",
     *  statusCodes={
     *         200="Returned when notification was remove",
     *         401="Returned when user not authenticated",
     *         404="Returned when userNotification not found with that id"
     *  },
     *
     * )
     *
     * @Secure(roles="ROLE_USER")
     * @param $userNotificationId
     * @return Response
     */
    public function deleteAction($userNotificationId)
    {
        $em = $this->getDoctrine()->getManager();
        $userNotification = $em->getRepository('ApplicationUserBundle:UserNotification')->findUserNotification($userNotificationId);

        if (is_null($userNotification)){
            throw new HttpException(Response::HTTP_NOT_FOUND, 'User notification with such id not found');
        }

        $notification = $userNotification->getNotification();

        $notification->removeUserNotification($userNotification);
        $em->remove($userNotification);

        if ($notification->getUserNotifications()->count() == 0){
            $em->remove($notification);
        }

        $em->flush();

        return new Response('ok');
    }
}