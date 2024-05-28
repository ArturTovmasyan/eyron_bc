<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PushNoteTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bl:push:test_note')
            ->setDescription('Send Test Notifications By UserName')
            ->setDefinition(array(new InputArgument('username', InputArgument::REQUIRED, 'The username')))
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $sendNoteService = $this->getContainer()->get('bl_put_notification_service');

        $username   = $input->getArgument('username');
        $user = $em->getRepository('ApplicationUserBundle:User')->findOneby(array('username' => $username));

        if($user){
            $sendNoteService->sendTestMassage($user, null, null);
            $output->writeln(" <info>Success.Test Message send to ".$username." user</info>");
        }else{
            $output->writeln("<error>Sorry,user by ".$username." username does not exist</error>");
        }

    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a username:',
                function($username) {
                    if (empty($username)) {
                        throw new \Exception('Username can not be empty');
                    }

                    return $username;
                }
            );
            $input->setArgument('username', $username);
        }
    }
}
