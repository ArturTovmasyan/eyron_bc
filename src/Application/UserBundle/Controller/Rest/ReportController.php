<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 8/2/16
 * Time: 1:55 PM
 */
namespace Application\UserBundle\Controller\Rest;

use Application\UserBundle\Entity\Report;
use Application\UserBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


/**
 * @Rest\RouteResource("Report")
 * @Rest\Prefix("/api/v1.0")
 */
class ReportController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Report",
     *  description="This function is used to create a report",
     *  statusCodes={
     *         200="Return when report was sent",
     *         400="Return when data is invalid",
     *         404="Return when reported user or report content not found"
     *     },
     * parameters={
     *      {"name"="reportType", "dataType"="integer", "required"=true, "description"="Reporte type (comment, success story, ...)"},
     *      {"name"="contentType", "dataType"="integer", "required"=true, "description"="Reported content type (comment, success story, ...)"},
     *      {"name"="contentId",   "dataType"="string",  "required"=true, "description"="Reported content id"},
     *      {"name"="message",     "dataType"="string",  "required"=true, "Report description"}
     * }
     * )
     * @Rest\View()
     * @Secure("ROLE_USER")
     *
     * @param Request $request
     * @return array
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if($request->getContentType() == 'application/json' || $request->getContentType() == 'json'){
            $content = (array) json_decode($request->getContent());
            $request->request->add($content);
        }
        $currentUser = $this->getUser();

        $report = $em->getRepository("ApplicationUserBundle:Report")
            ->findBy(array(
                'user'          => $currentUser,
                'contentId'     => $request->get('contentId', null),
                'contentType'   => $request->get('contentType', null)
            ));

        if(count($report) == 0){
            $report = new Report();
        } else{
            $report = $report[0];
        }

        $report->setUser($currentUser);
        $report->setReportType($request->get('reportType', null));
        $report->setContentType($request->get('contentType', null));
        $report->setContentId($request->get('contentId', null));
        $report->setMessage($request->get('message', null));

        $validator = $this->get('validator');
        $errors = $validator->validate($report);

        if(count($errors) > 0){
            throw new HttpException(Response::HTTP_BAD_REQUEST, $errors[0]->getMessage());
        }

        switch($report->getContentType()){
            case Report::COMMENT:
                $comment = $em->getRepository('ApplicationCommentBundle:Comment')->findCommentWithAuthor($report->getContentId());

                if (is_null($comment)){
                    throw new HttpException(Response::HTTP_NOT_FOUND, 'comment not found');
                }

                $report->setReportedUser($comment->getAuthor());
                break;

            case Report::SUCCESS_STORY:
                $story = $em->getRepository('AppBundle:SuccessStory')->findStoryWithAuthor($report->getContentId());
                if (is_null($story)){
                    throw new HttpException(Response::HTTP_NOT_FOUND, 'story not found');
                }

                $report->setReportedUser($story->getUser());
                break;
        }

        $em->persist($report);
        $em->flush();

        return [];
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Report",
     *  description="This function is used to get a report",
     *  statusCodes={
     *         200="Return when report was get",
     *         400="Return when data is invalid",
     *         404="Return when reported user or report content not found"
     *     },
     * )
     *
     * @Secure("ROLE_USER")
     * @Rest\View(serializerGroups={"report"})
     *
     * @param Request $request
     * @return array
     */
    public function getAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $report = $em->getRepository("ApplicationUserBundle:Report")
            ->findBy(array(
                'user'          => $this->getUser(),
                'contentId'     => $request->get('commentId'),
                'contentType'   => $request->get('type')
            ));

        return (count($report) != 0)?$report[0]:null;
    }
}