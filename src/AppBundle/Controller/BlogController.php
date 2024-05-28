<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Blog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BlogController
 * @package AppBundle\Controller
 */
class BlogController extends Controller
{
    const LIMIT = 6;

    /**
     * @Route("/blog", name="blog_list")
     * @return Response
     * @param Request $request
     */
    public function listAction(Request $request)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //enable published filter for blog
        $em->getFilters()->enable('publish_filter');

        //get page number
        $page = $request->query->get('page');

        //generate first number by page
        if($page > 1) {
            $first = ($page - 1) * self::LIMIT;
        }
        else {
            $first = 0;
        }

        //get last published date for caching
        $lastModifiedDate = $em->getRepository('AppBundle:Blog')->findLastPublishedDate($first, self::LIMIT);

        //new response
        $response = new Response();

        // set last modified data
        $response->setLastModified($lastModifiedDate);

        // Set response as public. Otherwise it will be private by default.
        $response->setPublic();

        // Check that the Response is not modified for the given Request
        if ($response->isNotModified($request)) {
            // return the 304 Response immediately
            return $response;
        }

        //get all blog
        $blog = $em->getRepository('AppBundle:Blog')->findAllBlog();

        //get paginator
        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $blog,
            $request->query->getInt('page', 1)/*page number*/,
            self::LIMIT
        );

        return $this->render('AppBundle:Blog:list.html.twig', ['blogs' => $pagination, 'updated' => $lastModifiedDate], $response);
    }

    /**
     * @Route("/blog/comment/{id}/{slug}", requirements={"id"="\d+"}, name="blog_comment")
     * @return Response
     * @Template()
     * @param $id
     * @param $slug
     */
    public function blogCommentAction($id, $slug)
    {
        return array(
            'id'   => $id,
            'slug' => $slug
        );
    }

    /**
     * @param $slug
     * @param Request $request
     * @Template()
     * @Route("/blog/{slug}", name="blog_show")
     * @return Response
     */
    public function showAction(Request $request, $slug)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get blog
        $blog = $em->getRepository('AppBundle:Blog')->findBySlug($slug);

        if(is_null($blog)){
            throw $this->createNotFoundException("Blog not found");
        }

        //new response
        $response = new Response();

        // set last modified data
        $response->setLastModified($blog->getUpdated());

        // Set response as public. Otherwise it will be private by default.
        $response->setPublic();

        // Check that the Response is not modified for the given Request
        if ($response->isNotModified($request)) {
            // return the 304 Response immediately
            return $response;
        }

        //add goals in each array data in blog
        $goalIds = $blog->getRelatedGoalIds();
        $relatedGoals = $em->getRepository('AppBundle:Goal')->findGoalByIds($goalIds);
        $blog->addGoalsInData($relatedGoals);

        return $this->render('AppBundle:Blog:show.html.twig', ['blog' => $blog], $response);
    }

    /**
     * This action is used to include amp menu in blog page
     *
     * @Template()
     * @return array
     */
    public function ampMenuAction()
    {
        return [];
    }
}


