<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/16/15
 * Time: 8:40 PM
 */

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Builder
 * @package AppBundle\Menu
 */
class Builder implements ContainerAwareInterface
{
    private $otherMenu;

    private $policyMenu;

    private $container;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        // get doctrine manager
        $em = $this->container->get('doctrine')->getManager();
        $pages = $em->getRepository('AppBundle:Page')->findAllByOrdered();

        // check pages
        if($pages){

            // loop for pages
            foreach($pages as $page){
                if (strpos(strtolower($page->getName()), 'policy') === false && strpos(strtolower($page->getName()), 'Политика конфиденциальности') === false){
                    $this->otherMenu[] = $page;
                }
                else {
                    $this->policyMenu[] = $page;
                }
            }
        }
    }

    /**
     * @param FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\ItemInterface
     */
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        // get e
        $menu = $factory->createItem('root');

        //get translator
        $tr = $this->container->get('translator');

        // check pages
        if($this->otherMenu){

            // loop for all pages
            foreach($this->otherMenu as $page)
            {
                // add menu
                $menu->addChild($page->getName(), array('route' => 'page', 'routeParameters' => array('slug' => $page->getSlug())))->setExtra('translation_domain', false);
            }

            $menu->addChild($tr->trans('menu.bucketlist_stories'), array('route' => 'blog_list'))->setExtra('translation_domain', false);
        }

        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\ItemInterface
     */
    public function privacyMenu(FactoryInterface $factory, array $options)
    {
        // get menu
        $menu = $factory->createItem('root');

        // check pages
        if($this->policyMenu){

            // loop for all pages
            foreach($this->policyMenu as $page){

                // add menu
                $menu->addChild($page->getName(), array(
                    'route' => 'page',
                    'routeParameters' => array('slug' => $page->getSlug())))->setExtra('translation_domain', false);
            }
        }

        return $menu;
    }
}