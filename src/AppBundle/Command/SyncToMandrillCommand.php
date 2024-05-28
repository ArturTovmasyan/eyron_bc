<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 5/24/16
 * Time: 12:03 PM
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class SyncToMandrillCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('bl:sync:mandrill')
            ->setDescription('Synchronize our users with mandrill');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // get mail chimp service
        $mailChimp = $this->getContainer()->get('bl_mail_chimp_service');

        //get all users
        $users = $em->getRepository('ApplicationUserBundle:User')->findAllForMandrill();

        $output->writeln("<info>Starting</info>");

        $progress = new ProgressBar($output, count($users));
        $progress->start();

        //set default counter
        $counter = 0;

        // loop for data
        foreach($users as $user){

            // mail add subscriber
            $mailChimp->addSubscriber($user, '6e296b753f'); // list hash

            $counter++;
            $progress->advance();
        }

        $progress->finish();

        $output->writeln("<info></info>");
        $output->writeln("<info>Success</info>");

    }
}