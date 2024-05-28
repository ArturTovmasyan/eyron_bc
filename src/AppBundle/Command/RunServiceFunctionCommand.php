<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/19/16
 * Time: 4:33 PM
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class RunServiceFunctionCommand
 * @package AppBundle\Command
 */
class RunServiceFunctionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:run:service')
            ->setDescription('Run service function')
            ->addArgument('serviceName', InputArgument::REQUIRED, 'Class name of service')
            ->addArgument('function', InputArgument::REQUIRED, 'function name')
            ->addArgument('arguments', InputArgument::IS_ARRAY, 'Array of arguments')
            ->setHelp('Run service command by given service name, service function name and function arguments')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get container
        $container = $this->getContainer();

        $context = $container->get('router')->getContext();
        $context->setHost($container->getParameter('project_name'));

        $serviceName = $input->getArgument('serviceName'); // get service name
        $function = $input->getArgument('function'); // get function name
        $arguments = $input->getArgument('arguments'); // get arguments

        // get service
        $service = $container->get($serviceName);

        $arguments = array_map(function($item){
            $data = json_decode($item, true);
            return is_null($data) ? $item : $data;
        }, $arguments);

        // run service function
        call_user_func_array(array($service, $function), $arguments);

        return null;
    }
}