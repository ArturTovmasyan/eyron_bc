<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/19/16
 * Time: 5:30 PM
 */

namespace AppBundle\Services;

use Application\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Process\Process;

/**
 * Class AbstractProcessService
 * @package AppBundle\Services
 */
abstract class AbstractProcessService
{
    /**
     * @param $serviceName
     * @param $function
     * @param array $arguments
     */
    final protected function runAsProcess($serviceName, $function, array $arguments)
    {
        // implode arguments
        $arguments = implode(' ', $arguments);

        // generate command
        $command = __DIR__ . "/../../../app/console bl:run:service $serviceName $function $arguments";

        $process = new Process($command);
        $process->start();
    }

}