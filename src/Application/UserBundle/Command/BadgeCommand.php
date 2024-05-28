<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/20/16
 * Time: 3:54 PM
 */

namespace Application\UserBundle\Command;

use AppBundle\Entity\UserGoal;
use Application\UserBundle\Entity\Badge;
use Application\UserBundle\Entity\MatchUser;
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
class BadgeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:badge:calculate')
            ->setDescription('This command is used to calculate user badges')
            ->setDefinition(array(
                new InputOption(
                    'type', 't', InputOption::VALUE_REQUIRED,
                    'Type of badges  ( all|innovator|motivator)'
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
        if($type == 'all' || $type == 'innovator'){
            $this->calculateInnovator($input, $output);
            $exec = true;
        }

        // che
        if($type == 'all' || $type == 'motivator'){
            $this->calculateMotivator($input, $output);
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
    private function calculateInnovator(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $output->writeln('start calculate innovator');

        $badgeService = $this->getContainer()->get('bl.badge.service');

        $goals = $em->createQuery("SELECT g
                           FROM AppBundle:Goal g
                           JOIN g.userGoal ug
                           JOIN g.author a
                           WHERE a.isAdmin != 1 AND g.publish = 1
                           ")
        ->getResult();

        $progress = new ProgressBar($output, count($goals));

        $progress->start();

        foreach ($goals as $goal){

            $score = 0;

            // get author
            $author = $goal->getAuthor(); // get author
            $publish = $goal->getPublish();

            // check is admin
            if($publish && $author && !$author->isAdmin()){
                $score ++;

                $userGoals = $goal->getUserGoal();

                // check status
                foreach ($userGoals as $userGoal){

                    $status = $userGoal->getStatus();
                    if($status == UserGoal::ACTIVE){
                        $score += 1;
                    }elseif($status == UserGoal::COMPLETED){
                        $score += 2;
                    }
                }
                $badgeService->addScore(Badge::TYPE_INNOVATOR, $author->getId(), $score, false);

            }

            $progress->advance();


        }

        $progress->finish();

        $output->writeln('end calculate innovator');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function calculateMotivator(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $output->writeln('start calculate motivator');

        $badgeService = $this->getContainer()->get('bl.badge.service');

        $successStories = $em->createQuery("SELECT s
                           FROM AppBundle:SuccessStory  s
                           JOIN s.goal g
                           JOIN g.userGoal ug
                           WHERE g.publish = 1
                           ")
            ->getResult();

        $progress = new ProgressBar($output, count($successStories));

        $progress->start();

        foreach ($successStories as $successStory){

            $goal = $successStory->getGoal();
            // get author
            $author = $successStory->getUser(); // get author
            $publish = $goal->getPublish();

            $voters = $successStory->getVotersCount();

            // check is admin
            if($publish && $author && $voters > 0){

                $badgeService->addScore(Badge::TYPE_MOTIVATOR, $author->getId(), $voters, false);
            }

            $progress->advance();
        }

        $progress->finish();

        $output->writeln('end calculate motivator');

    }

}