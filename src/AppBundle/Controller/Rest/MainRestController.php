<?php

namespace AppBundle\Controller\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\Prefix("/api/v1.0")
 */
class MainRestController extends FOSRestController
{
    const IOS_REQUEST_PARAM     = 'ios';
    const ANDROID_REQUEST_PARAM = 'android';

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Main",
     *  description="This function is used to get mobile last versions",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     *  parameters={
     *      {"name"="mobileAppPlatform", "dataType"="string", "required"=true, "description"="mobile app platform"}
     *  })
     *
     * @param $mobileAppPlatform
     * @return array
     * @Rest\View
     */
    public function getAppVersionAction($mobileAppPlatform)
    {
        switch($mobileAppPlatform){
            case MainRestController::IOS_REQUEST_PARAM:
                return [
                    'mandatory' => $this->container->getParameter('ios_mandatory_version'),
                    'optional'  => $this->container->getParameter('ios_optional_version')
                ];
            case MainRestController::ANDROID_REQUEST_PARAM:
                return [
                    'mandatory' => $this->container->getParameter('android_mandatory_version'),
                    'optional'  => $this->container->getParameter('android_optional_version')
                ];
        }

        return [];
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Main",
     *  description="This function is used to get bottom menu list",
     *  statusCodes={
     *         200="OK",
     *  })
     *
     * @Rest\View
     */
    public function getBottomMenuAction()
    {
        $menu = [];

        // get doctrine manager
        $em = $this->container->get('doctrine')->getManager();

        $tr = $this->get('translator');

        //get all page
        $pages = $em->getRepository('AppBundle:Page')->findAllByOrdered();

        //get router service
        $router = $this->get('router');

        // check pages
        if ($pages) {

            // loop for pages
            foreach ($pages as $page)
            {
                $menu[] = ['name' => $page->getName(), 'url' => $router->generate('page', ['slug' => $page->getSlug()], true),
                    'slug' => $page->getSlug(), 'isTerm' => $page->getIsTerm()];
            }
            $menu[] = ['name' => $tr->trans('menu.bucketlist_stories'), 'url' => $router->generate('blog_list', [], true), 'isTerm' => false];
        }

        return new JsonResponse($menu);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Main",
     *  description="This function is used to get page data by slug and locale",
     *  statusCodes={
     *         200="OK",
     *  })
     *
     * @Rest\Get("/pages/{slug}/{locale}", name="app_rest_goal_title", options={"method_prefix"=false})
     * @param $slug
     * @param $locale
     * @return JsonResponse
     */
    public function getPageAction($slug, $locale = 'en')
    {
        //set default array data
        $data = [];

        // get doctrine manager
        $em = $this->container->get('doctrine')->getManager();

        //get page data by page name
        $pageData = $em->getRepository("AppBundle:Page")->findOneBy(['slug' => $slug]);

        if($locale == 'en') {

            $data[] = ['name' => $pageData->getName(), 'title' => $pageData->getTitle(), 'description' => $pageData->getDescription()];
        }
        else {

            //set default null value
            $name = null;
            $description = null;
            $title = null;

            //get page translations data
            $translations = $pageData->getTranslations();

            foreach ($translations as $trans)
            {
                if ($trans->getLocale() == $locale) {

                    if ($trans->getField() == 'name') {
                        $name = $trans->getContent();
                    }

                    if ($trans->getField() == 'description') {
                        $description = $trans->getContent();
                    }

                    if ($trans->getField() == 'title') {
                        $title = $trans->getContent();
                    }
                }
            }

            $data[] = ['name' => $name, 'title' => $title, 'description' => $description];
        }

        return new JsonResponse($data);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Main",
     *  description="This function is used to send contact us email",
     *  statusCodes={
     *         200="OK",
     *  })
     *
     * @Rest\Post("/contact/send-email", name="post_contact_us", options={"method_prefix"=false})
     * @param $request
     * @return Response
     */
    public function postContactUsAction(Request $request)
    {
        // get doctrine manager
        $em = $this->container->get('doctrine')->getManager();

        //get all emails user data
        $admins = $em->getRepository('ApplicationUserBundle:User')->findAdmins('ROLE_SUPER_ADMIN');

        //check if request content type is json
        if ($request->getContentType() == 'application/json' || $request->getContentType() == 'json') {
            //get content and add it in request after json decode
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        //get emailData
        $emailData = $request->get('emailData');

        foreach ($admins as $admin)
        {
            $this->get('bl.email.sender')->sendContactUsEmail($admin['email'], $admin['fullName'], $emailData);
        }

        return new Response('', Response::HTTP_OK);
    }
}