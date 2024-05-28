<?php

namespace AppBundle\Listener;


use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Class MyEntityListenerResolver
 * @package AppBundle\Listener
 */
class MyEntityListenerResolver extends DefaultEntityListenerResolver
{

    /**
     * @var Container
     */
    public $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($className)
    {
        //get stopwatch component
        $stopwatch = $this->container->get('debug.stopwatch');

        // Start event named 'eventName'
        $stopwatch->start('bl_my_entity_listener_resolver');

        // get object
        $object = parent::resolve($className);

        // check object, and set container
        if($object instanceof ContainerAwareInterface){
            $object->setContainer($this->container);
        }

        // Start event named 'eventName'
        $stopwatch->start('bl_my_entity_listener_resolver');

        return $object;
    }

}