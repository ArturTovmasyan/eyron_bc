<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 6/15/16
 * Time: 3:13 PM
 */
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewsFeedCountsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:news_feed:counts')
            ->setDescription('Set news feed listedBy and doneBy');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();


        $newsFeedsCount = $em->createQuery("SELECT COUNT(n) FROM AppBundle:NewFeed n JOIN n.goal g")
                        ->getSingleScalarResult();

        $progress = new ProgressBar($output, $newsFeedsCount);
        $progress->start();


        $first = 0;
        $count = 50;
        do{
            $newsFeeds = $em->createQuery("SELECT n FROM AppBundle:NewFeed n JOIN n.goal g")
                ->setFirstResult($first)
                ->setMaxResults($count)
                ->getResult();

            $first += 50;

            $goalIds = [];
            foreach($newsFeeds as $newsFeed){
                $goalIds[$newsFeed->getGoal()->getId()] = 1;
            }

            $stats = $em->getRepository("AppBundle:Goal")->findGoalStateCount($goalIds, true);

            foreach($newsFeeds as $newsFeed){
                $newsFeed->setListedBy($stats[$newsFeed->getGoal()->getId()]['listedBy']);
                $newsFeed->setCompletedBy($stats[$newsFeed->getGoal()->getId()]['doneBy']);

                $progress->advance();
            }

            $em->flush();
        }
        while(count($newsFeeds) > 0);


        $progress->finish();
        $output->writeln("<info>Success</info>");
    }
}