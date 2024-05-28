<?php

namespace AppBundle\Twig\Extension;

/**
 * Class ImageFilterExtension
 * @package AppBundle\Twig\Extension
 */
class ImageFilterExtension extends \Twig_Extension
{
    /**
     * @var
     */
    private $container;

    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('blImageFilter', array($this, 'blImageFilter')),
            new \Twig_SimpleFilter('blImageFilterForCli', array($this, 'blImageFilterForCli')),
        );
    }


    /**
     * @param $path
     * @param $filter
     */
    public function blImageFilter($path, $filter)
    {

        // check has http in path
        if(strpos($path, 'http') === false){

            try{
                $request = $this->container->get('request_stack')->getCurrentRequest();
                $this->container->get('liip_imagine.controller')->filterAction($request, $path, $filter);
                $cacheManager = $this->container->get('liip_imagine.cache.manager');
                $srcPath = $cacheManager->getBrowserPath($path, $filter);

                return $srcPath;
            }catch (\Exception $e){
                return $path;
            }
        }
        else{
            return $path;
        }
    }

    /**
     * @param $path
     * @param $filter
     * @return mixed
     */
    public function blImageFilterForCli($path, $filter)
    {
        $baseUrl = $this->container->getParameter('protocol') . '://';
        $baseUrl .= $this->container->getParameter('project_name');

        // check has http in path
        if(strpos($path, 'http') === false){

            try{
                $route = $this->container->get('router');
                $liipManager = $this->container->get('liip_imagine.cache.manager');
                $liipManager->getBrowserPath($path, $filter);
                $params = ['path' => ltrim($path, '/'), 'filter' => $filter];
                $srcPath = $route->generate('liip_imagine_filter', $params);

                return $baseUrl . $srcPath;
            }catch (\Exception $e){
                return $baseUrl . $path;
            }
        }
        else{
            return $baseUrl . $path;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_bl_image_filter';
    }
}