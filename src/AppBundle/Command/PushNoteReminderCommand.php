<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class PushNoteReminderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bl:push:note_reminder')
            ->setDescription('Send notification by coming doDates');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $sendNoteService = $this->getContainer()->get('bl_put_notification_service');

        $time = new \DateTime('now');
        $time = $time->format('H');
        $activeUsers = $em->getRepository('ApplicationUserBundle:User')->findby(array('activeTime' => $time));
        if($activeUsers){
            foreach($activeUsers as $user){
                $sendNoteService->sendReminderMassage($user);
            }
            $output->writeln("<info>Success.We push notification ".count($activeUsers)." users</info>");
        }else{
            $output->writeln("<error>Sorry.In this time we have not active users</error>");
        }
    }
}
