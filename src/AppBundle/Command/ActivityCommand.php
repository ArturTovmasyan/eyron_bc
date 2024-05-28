<?php

namespace AppBundle\Command;

use AppBundle\Entity\NewFeed;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActivityCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:activity:group')
            ->setDescription('This command change existing activities to grouped activities');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $first = 0;
        $count = 150;
        $em = $this->getContainer()->get('doctrine')->getManager();

        $newFeedCount = $em->createQuery("SELECT COUNT(n) FROM AppBundle:NewFeed n JOIN n.goal g")->getSingleScalarResult();

        $progress = new ProgressBar($output, $newFeedCount);
        $progress->start();

        do {

            $newFeeds = $em
                ->createQuery("SELECT n, g
                               FROM AppBundle:NewFeed n
                               JOIN n.goal g")
                ->setFirstResult($first)
                ->setMaxResults($count)
                ->getResult();


            foreach ($newFeeds AS $newFeed) {
                $date = $newFeed->getDatetime();
                $goal = $newFeed->getGoal();
                $goal->setStats([
                   'listedBy' => $newFeed->getListedBy(),
                   'doneBy'   => $newFeed->getCompletedBy(),
                ]);

                $newFeed->addGoal($newFeed->getGoal());
                $newFeed->setDatetime($date);
                $progress->advance();
            }

            $em->flush();
            $em->clear();
            $first += $count;
        }
        while(count($newFeeds) > 0);

        $progress->finish();
        $output->writeln('success');
    }
}