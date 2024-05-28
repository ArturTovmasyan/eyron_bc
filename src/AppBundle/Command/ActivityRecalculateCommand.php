<?php

namespace AppBundle\Command;

use AppBundle\Entity\NewFeed;
use AppBundle\Entity\UserGoal;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActivityRecalculateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:activity:recalculate')
            ->setDescription('Group old activities')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::OPTIONAL, 'The username of the user.')
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $em = $this->getContainer()->get('doctrine')->getManager();

        $users = [];
        if ($username){
            $user = $em->getRepository('ApplicationUserBundle:User')->findOneByUsername($username);
            if (is_null($user)){
                $output->writeln("<error>User Not found</error>");
                return;
            }

            $users[] = $user;
        }
        else {
            $first = 0;
            $count = 5;
            $users = $em->getRepository('ApplicationUserBundle:User')->findBy([], [], $count, $first);
        }

        $allCount = $em->createQuery("SELECT COUNT(u.id) FROM ApplicationUserBundle:User u")->getSingleScalarResult();

        if (!$username) {
            $progress = new ProgressBar($output, $allCount);
            $progress->start();
        }

        while(count($users) > 0)
        {
            foreach ($users as $user) {
                $em->createQuery("DELETE FROM AppBundle:NewFeed n WHERE n.user = :user
                                    AND (n.action = :addAction OR n.action = :completeAction OR n.action = :createAction)")
                    ->setParameter('user', $user->getId())
                    ->setParameter('addAction', NewFeed::GOAL_ADD)
                    ->setParameter('completeAction', NewFeed::GOAL_COMPLETE)
                    ->setParameter('createAction', NewFeed::GOAL_CREATE)
                    ->execute();

                $userGoals = $em->getRepository('AppBundle:UserGoal')->findAllByUserId($user->getId());

                $goalIds = [];
                foreach ($userGoals as $userGoal) {
                    $goalIds[$userGoal->getGoal()->getId()] = 1;
                }

                $stats = $em->getRepository("AppBundle:Goal")->findGoalStateCount($goalIds, true);

                foreach ($userGoals as $userGoal) {
                    $userGoal->getGoal()->setStats([
                        'listedBy' => $stats[$userGoal->getGoal()->getId()]['listedBy'],
                        'doneBy' => $stats[$userGoal->getGoal()->getId()]['doneBy'],
                    ]);
                }


                $lastData = [
                    UserGoal::ACTIVE => [
                        'newFeed' => null,
                        'lastId' => null,
                        'action' => NewFeed::GOAL_ADD
                    ],
                    UserGoal::COMPLETED => [
                        'newFeed' => null,
                        'lastId' => null,
                        'action' => NewFeed::GOAL_COMPLETE
                    ]
                ];

                foreach ($userGoals as $userGoal) {

                    if (is_null($lastData[$userGoal->getStatus()]['newFeed']) || abs($lastData[$userGoal->getStatus()]['lastId'] - $userGoal->getId()) > 100) {
                        $lastData[$userGoal->getStatus()]['newFeed'] = new NewFeed($lastData[$userGoal->getStatus()]['action'], $user, $userGoal->getGoal());
                        $performDate = $userGoal->getListedDate() ? $userGoal->getListedDate() : $userGoal->getCompletionDate();

                        if (is_null($performDate)) {
                            $performDate = $this->findApproximatelyDate($em, $userGoal->getId());
                        }

                        $lastData[$userGoal->getStatus()]['newFeed']->setDatetime($performDate);
                        $em->persist($lastData[$userGoal->getStatus()]['newFeed']);
                    } else {
                        $lastPerformDate = $lastData[$userGoal->getStatus()]['newFeed']->getDateTime();
                        $lastData[$userGoal->getStatus()]['newFeed']->addGoal($userGoal->getGoal());

                        $performDate = $userGoal->getListedDate() ? $userGoal->getListedDate() : $userGoal->getCompletionDate();
                        if (!is_null($performDate) && $lastPerformDate < $performDate) {
                            $lastData[$userGoal->getStatus()]['newFeed']->setDateTime($performDate);
                        } else {
                            $lastData[$userGoal->getStatus()]['newFeed']->setDateTime($lastPerformDate);
                        }
                    }

                    $lastData[$userGoal->getStatus()]['lastId'] = $userGoal->getId();
                }


                $em->flush();

                if (isset($progress)) {
                    $progress->advance();
                }
            }

            $em->clear();

            if (isset($first) && isset($count)){
                $first += $count;
                $users = $em->getRepository('ApplicationUserBundle:User')->findBy([], [], $count, $first);
            }
            else {
                break;
            }
        }


        if (isset($progress)) {
            $progress->finish();
        }
        $output->writeln("<info>Success</info>");
    }

    /**
     * @param $em
     * @param $userGoalId
     * @return \DateTime
     */
    private function findApproximatelyDate($em, $userGoalId)
    {
        $performDate = $em->createQuery("SELECT COALESCE(ug.listedDate, ug.completionDate)
                          FROM AppBundle:UserGoal ug
                          WHERE ug.id > :id
                          AND (ug.listedDate IS NOT NULL OR ug.completionDate IS NOT NULL)")
            ->setParameter('id', $userGoalId)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (is_null($performDate)){
            $performDate = $em->createQuery("SELECT COALESCE(ug.listedDate, ug.completionDate)
                          FROM AppBundle:UserGoal ug
                          WHERE ug.id < :id
                          AND (ug.listedDate IS NOT NULL OR ug.completionDate IS NOT NULL)")
                ->setParameter('id', $userGoalId)
                ->setMaxResults(1)
                ->getOneOrNullResult();
        }

        if (is_null($performDate)){
            return new \DateTime();
        }

        return new \DateTime($performDate[1]);
    }
}