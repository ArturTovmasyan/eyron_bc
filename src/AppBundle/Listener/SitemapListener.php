<?php

namespace AppBundle\Listener;

use AppBundle\Entity\Goal;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;

use Presta\SitemapBundle\Service\SitemapListenerInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\Stopwatch\Stopwatch;

class SitemapListener implements SitemapListenerInterface
{
    const SITEMAP_DEFAULT = 'default';
    const SITEMAP_BLOG = 'blog';

    private $event;

    private $router;

    private $em;

    private $stopwatch;


    public function __construct(RouterInterface $router, EntityManager $em, Stopwatch $stopwatch)
    {
        $this->router = $router;
        $this->em = $em;
        $this->stopwatch = $stopwatch;
    }

    public function populateSitemap(SitemapPopulateEvent $event)
    {
        //get stopwatch component
        $stopwatch = $this->stopwatch;

        // Start event named 'eventName'
        $stopwatch->start('bl_sitemap_listener');

        $this->event = $event;

        $section = $this->event->getSection();

        if (is_null($section) || $section == 'default' || $section == 'blog') {

            //get absolute homepage url
            $url = $this->router->generate('homepage', array(), true);

            //add homepage url to the url set named default
            $this->createSitemapEntry($url, new \DateTime(), UrlConcrete::CHANGEFREQ_YEARLY, 1, static::SITEMAP_DEFAULT);

            // get all goals
            $goals = $this->em->getRepository('AppBundle:Goal')->findBy(array('publish' => Goal::PUBLISH));

            foreach ($goals as $goal) {

                $slug = $goal->getSlug();
                $url = $this->router->generate('inner_goal', array('slug' => $slug), true);
                $tagUpdatedDate = $goal->getUpdated()->format("Y-m-d H:i:s");

                //add goal url to the url set named default
                $this->createSitemapEntry($url, new \DateTime($tagUpdatedDate), UrlConcrete::CHANGEFREQ_YEARLY, 0.8, static::SITEMAP_DEFAULT);
            }

            //get all blogs
            $blogs = $this->em->getRepository('AppBundle:Blog')->findAll();

            foreach ($blogs as $blog)
            {
                $slug = $blog->getSlug();

                $url = $this->router->generate('blog_show', array('slug' => $slug), true);

                $blogUpdatedDate = $blog->getUpdated()->format("Y-m-d H:i:s");

                //add blog url to the url set named blog
                $this->createSitemapEntry($url, new \DateTime($blogUpdatedDate), UrlConcrete::CHANGEFREQ_YEARLY, 0.9, static::SITEMAP_BLOG);
            }
        }
        // Start event named 'eventName'
        $stopwatch->stop('bl_sitemap_listener');
    }

    private function createSitemapEntry($url, $modifiedDate, $changeFrequency, $priority, $name)
    {
        //add homepage url to the urlset named default
        $this->event->getGenerator()->addUrl(
            new UrlConcrete(
                $url,
                $modifiedDate,
                $changeFrequency,
                $priority
            ),
            $name
        );
    }
}

