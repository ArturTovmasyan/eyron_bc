<?php

namespace AppBundle\Command;

use AppBundle\Entity\NewFeed;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetIsAdminCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:set:is-admin')
            ->setDescription('This command is used to set users isAdmin field value');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $users = $em->getRepository('ApplicationUserBundle:User')->findAll();

        $progress = new ProgressBar($output, count($users));
        $progress->start();

        foreach ($users as $user) {
            $user->setIsAdmin($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN') || $user->hasRole('ROLE_GOD'));
            $progress->advance();
        }

        $em->flush();

        $progress->finish();
        $output->writeln('success');
    }
}