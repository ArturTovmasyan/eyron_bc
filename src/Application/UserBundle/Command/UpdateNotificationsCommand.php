<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 9/5/16
 * Time: 3:19 PM
 */
namespace Application\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateNotificationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:notification:change_structure')
            ->setDescription('This command is used to change notifications structure')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $notifications = $em->getRepository('ApplicationUserBundle:Notification')->findAll();

        $progress = new ProgressBar($output, count($notifications));
        $progress->start();

        foreach($notifications as $notification){
            $goalSlug = explode('/', $notification->getLink());
            $goalSlug = array_pop($goalSlug);

            $goal = $em->getRepository('AppBundle:Goal')->findOneBySlug($goalSlug);
            if ($goal) {
                $notification->setGoalId($goal->getId());
            }

            $body = strip_tags($notification->getBody());
            if ($notification->getPerformer()) {
                $body = str_replace($notification->getPerformer()->showName(), '', $body);
            }
            $notification->setBody($body);

            $progress->advance();
        }

        $progress->finish();
        $em->flush();

        $output->writeln('Success');
    }
}
