<?php

namespace AppBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;



class MandrillService
{

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected  $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $email
     * @param $name
     * @param $subject
     * @param $message
     * @throws \Exception
     * @throws \Mandrill_Error
     */
    public function sendEmail($email, $name, $subject, $message)
    {
        // get mandrill app key
        $mandrillAppKey = $this->container->getParameter('mandrill_api_key');

        //get from email in parameter
        $fromEmail = $this->container->getParameter('to_report_email');

        // get get environment
        $env = $this->container->get('kernel')->getEnvironment();

        $projectName = $this->container->getParameter('email_sender');

        // check environment
        if($env == "test"){
            return;
        }

        try {

            $mandrill = new \Mandrill($mandrillAppKey);
            $message = array(
                'html' => $message,
                'subject' => $subject,
                'from_email' => $fromEmail,
                'from_name' => $projectName,
                'to' => array(
                    array(
                        'email' => $email,
                        'name' => $name,
                        'type' => 'to'
                    )
                )
            );
            $async = false;
            $ip_pool = 'Main Pool';
            $mandrill->messages->send($message, $async, $ip_pool);

        } catch(\Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            $monoLog = $this->container->get("monolog.logger.mandrill");

            $monoLog->error('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
        }
    }

}