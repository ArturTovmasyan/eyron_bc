<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/7/16
 * Time: 3:12 PM
 */

namespace Application\UserBundle\Command;
use AppBundle\Entity\UserGoal;
use AppBundle\Services\UserNotifyService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Class BadgeCommand
 * @package Application\UserBundle\Command
 */
class SendNotifyCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:user-notify:send')
            ->setDescription('This command is used to send user notify')
            ->setDefinition(array(
                new InputOption(
                    'type', 't', InputOption::VALUE_REQUIRED,
                    'Type of notify  ( deadline|goal-friends|new-idea)'
                ),
            ));
        ;
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get type
        $type = $input->getOption('type');
        $exec = false;
        // check type
        if($type == 'deadline'){
            $this->sendDeadlineNotify($input, $output);
            $exec = true;
        }
        if($type == 'goal-friends'){
            $this->sendNewGoalFriendNotify($input, $output);
            $exec = true;
        }

        if($type == 'new-idea'){
            $this->sendNewIdeaNotify($input, $output);
            $exec = true;
        }
        if(!$exec){
            $output->writeln('<error>Wrong type</error>');
            return false;
        }
        return null;
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function sendDeadlineNotify(InputInterface $input, OutputInterface $output)
    {
        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        $output->writeln('start sending notify');
        $userNotify = $this->getContainer()->get('user_notify'); // get user notify service
        $today = new \DateTime(); // new date

        // get deadlines
        $userGoals = $em->createQuery("SELECT ug
                           FROM AppBundle:UserGoal ug
                           JOIN ug.goal g   
                           WHERE ug.doDate is not null AND DATE_DIFF(:today, ug.doDate) > 1 AND g.publish = 1
                           ")
            ->setParameter('today', $today)
            ->getResult();

        $progress = new ProgressBar($output, count($userGoals));

        $progress->start();
        foreach ($userGoals as $userGoal){

            $user = $userGoal->getUser();
            $goal = $userGoal->getGoal();

            // send email
            $userNotify->prepareAndSendNotifyViaProcess($user->getId(), UserNotifyService::DEADLINE, ['goalId' => $goal->getId()], true);

            $progress->advance();
        }
        $progress->finish();
        $output->writeln('end sending notify');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function sendNewGoalFriendNotify(InputInterface $input, OutputInterface $output)
    {
        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        $output->writeln('start sending notify');
        $userNotify = $this->getContainer()->get('user_notify'); // get user notify service
        $today = new \DateTime(); // new date

        // get all users
        $users = $em->createQuery("SELECT u.id
                           FROM ApplicationUserBundle:User u
                           ")
            ->getResult();

        $users = array_map(function ($item){return $item['id'];}, $users);

        $progress = new ProgressBar($output, count($users));

        $progress->start();
        foreach ($users as $id){

            $friendsCount = $em
                ->createQueryBuilder()
                ->select('count(DISTINCT u)')
                ->from('ApplicationUserBundle:User', 'u', 'u.id')
                ->join('u.userGoal', 'ug')
                ->join('AppBundle:UserGoal', 'ug1', 'WITH', 'ug1.goal = ug.goal AND ug1.user = :userId')
                ->where("u.id != :userId AND u.isAdmin = false")
                ->andWhere("DATE_DIFF(:today, ug1.listedDate) < 30 ")
                ->setParameter('today', $today)
                ->setParameter('userId', $id)
                ->getQuery()
                ->getSingleScalarResult();

            if($friendsCount){
                // send email
                $userNotify->prepareAndSendNotifyViaProcess($id, UserNotifyService::NEW_GOAL_FRIEND, ['count' => $friendsCount], true);
                $progress->advance();
            }


        }
        $progress->finish();

        $output->writeln('end sending notify');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function sendNewIdeaNotify(InputInterface $input, OutputInterface $output)
    {
        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        $output->writeln('start sending notify');
        $userNotify = $this->getContainer()->get('user_notify'); // get user notify service
        $today = new \DateTime(); // new date

        // get goals
        $goalsCount = $em->createQuery("SELECT count(g)
                           FROM AppBundle:Goal g
                           WHERE g.created is not null AND DATE_DIFF(:today, g.created ) < 30 AND g.publish = 1
                           ")
            ->setParameter('today', $today)
            ->getSingleScalarResult();

        if($goalsCount > 0){
            // get all users
            $users = $em->createQuery("SELECT u.id
                           FROM ApplicationUserBundle:User u
                           ")
                ->getResult();

            $users = array_map(function ($item){return $item['id'];}, $users);

            $progress = new ProgressBar($output, count($users));

            $progress->start();
            foreach ($users as $id){

                // send email
                $userNotify->prepareAndSendNotifyViaProcess($id, UserNotifyService::NEW_IDEA, ['count' => $goalsCount], true);

                $progress->advance();
            }
            $progress->finish();
        }


        $output->writeln('end sending notify');
    }
}
