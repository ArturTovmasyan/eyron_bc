<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 9/22/16
 * Time: 11:33 AM
 */
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveTagSymbolCommand extends ContainerAwareCommand
{
    const USER_LIMIT = 1000;

    protected function configure()
    {
        $this
            ->setName('bl:remove:tag_symbols')
            ->setDescription('This function is used to remove tags # symbol from goal description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getFIlters()->disable('archived_goal_filter');

        $allCount = $em->createQuery("SELECT COUNT(g) FROM AppBundle:Goal g WHERE g.description LIKE '%#%'")->getSIngleScalarResult();

        $first = 0;
        $count = 100;

        $progress = new ProgressBar($output, $allCount);
        $progress->start();

        do {
            $goals = $em->createQuery("SELECT g FROM AppBundle:Goal g WHERE g.description LIKE '%#%'")
                ->setFirstResult($first)
                ->setMaxResults($count)
                ->getResult();

            foreach($goals as $goal){
                $description = $goal->getDescription();
                $description = preg_replace('/#(?!\s|#)/', '', $description);
                $goal->setDescription($description);

                $progress->advance();
            }

            $em->flush();
            $em->clear();
            $first += $count;
        } while(count($goals) > 0);

        $progress->finish();
        $output->writeln('Success!!');
    }
}