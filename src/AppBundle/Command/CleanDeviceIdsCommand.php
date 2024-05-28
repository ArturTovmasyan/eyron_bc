<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class CleanDeviceIdsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bl:clean:device-ids')
            ->setDescription('clean duplicate device ids ');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $users = $em->getRepository('ApplicationUserBundle:User')->findWithRelationsIds();
        $userNotify = $this->getContainer()->get('user_notify');

        $progress = new ProgressBar($output, count($users));
        $progress->start();

        if($users)
        {
            $count = $userNotify->uniqueDevice($users);

            $output->writeln("<info>Success.We clean device ids ".$count." users</info>");
        }
        else {
            $output->writeln("<error>Sorry.We have not users with device ids</error>");
        }

        $em->flush();
        $progress->finish();
    }
}