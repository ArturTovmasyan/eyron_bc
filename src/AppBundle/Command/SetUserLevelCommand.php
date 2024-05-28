<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetUserLevelCommand extends ContainerAwareCommand
{
    const USER_LIMIT = 500;
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:set:level')
            ->setDescription('Set users level ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $begin = 0;
        $badgesCount = 0;
        $em = $this->getContainer()->get('doctrine')->getManager();

        $badges = $em->getRepository("ApplicationUserBundle:Badge")->findAll();
        $progress = new ProgressBar($output, count($badges));
        $progress->start();

        do {
            $badges = $em->getRepository("ApplicationUserBundle:Badge")->findByLimit(self::USER_LIMIT * $begin++, self::USER_LIMIT);
            if ($badges) {
                foreach ($badges as $badge) {
                    $badge->getUser()->setLevel($badge->getType(), $badge->getScore() > 0);
                    $progress->advance();
                }
                $badgesCount += count($badges);
            }

            $em->flush();

        } while (count($badges));

        $progress->finish();
        $output->writeln('set level ' . $badgesCount . ' badges ');
    }
}