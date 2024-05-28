<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActiveTimeCommand extends ContainerAwareCommand
{
    const USER_LIMIT = 500;
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:set:active')
            ->setDescription('Set users active times ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $begin = 0;
        $usersCount = 0;
        $em = $this->getContainer()->get('doctrine')->getManager();

        $userCount = $em->getRepository('ApplicationUserBundle:User')->findAllCount();
        $progress = new ProgressBar($output, $userCount);
        $progress->start();

        do {
            $users = $em->getRepository('ApplicationUserBundle:User')->findByLimit(self::USER_LIMIT * $begin++, self::USER_LIMIT);
            if ($users) {
                foreach ($users as $user) {
                    $user->updateActiveTime();

                    $progress->advance();
                }
                $usersCount += count($users);
            }

            $em->flush();

        } while (count($users));

        $progress->finish();
        $output->writeln('set active time  ' . $usersCount . ' users ');
    }
}