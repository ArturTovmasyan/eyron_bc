<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 8/5/16
 * Time: 2:48 PM
 */
namespace Application\AffiliateBundle\Controller\Rest;

use AppBundle\Entity\Goal;
use AppBundle\Entity\GoalImage;
use Application\AffiliateBundle\Entity\AffiliateType;
use Application\CommentBundle\Entity\Comment;
use Application\CommentBundle\Entity\Thread;
use Application\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Rest\RouteResource("Affiliate")
 * @Rest\Prefix("/api/v1.0")
 */
class AffiliateController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Affiliate",
     *  description="This function is used to get page affiliates",
     *  statusCodes={
     *         200="Returned when all is ok",
     *         401="Returned when user not found",
     *         400="Body is empty or parent's thread isn't given parent"
     *     },
     * parameters={
     *      {"name"="page_link", "dataType"="string", "required"=true, "description"="Page link"},
     * }
     * )
     *
     * @Rest\View(serializerGroups={"affiliate"})
     *
     * @param Request $request
     * @return Response
     */
    public function postAction(Request $request)
    {
        if($request->getContentType() == 'application/json' || $request->getContentType() == 'json'){
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        $link = $request->get('page_link');
        $zone = $request->get('zone');
        
        if (is_null($link) || is_null($zone) || !in_array($zone, AffiliateType::getAllowedZones())){
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        AffiliateType::$bookingAId = $this->getParameter('booking_aid');

        $em = $this->getDoctrine()->getManager();
        $affiliates = $em->getRepository('ApplicationAffiliateBundle:Affiliate')->getAffiliatesByLink($link, $zone);

        return $affiliates;
    }
}