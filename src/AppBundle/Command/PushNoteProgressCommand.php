<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class PushNoteProgressCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bl:push:note_progress')
            ->setDescription('Send progress notification ');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $sendNoteService = $this->getContainer()->get('bl_put_notification_service');

        $activeUsers = $em->getRepository('ApplicationUserBundle:User')->findPushNoteUsers();

        $progress = new ProgressBar($output, count($activeUsers));
        $progress->start();

        if($activeUsers)
        {
            foreach($activeUsers as $user)
            {
                $sendNoteService->sendProgressMassage($user);
                $user->setLastPushNoteData(new \DateTime());
                $progress->advance();
            }

            $output->writeln("<info>Success.We push notification ".count($activeUsers)." users</info>");
        }
        else {
            $output->writeln("<error>Sorry.In this time we have not active users</error>");
        }

        $em->flush();
        $progress->finish();
    }
}