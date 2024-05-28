<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/29/16
 * Time: 1:31 PM
 */
namespace Application\UserBundle\Command;

use Application\UserBundle\Entity\MatchUser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ActiveFactorAndMatchesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:active_factor:match:calculator')
            ->setDescription('This command is used to calculate user active factor and match matches')
            ->addArgument('usersCount', InputArgument::OPTIONAL, 'Count of users')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $output->writeln('start');

        $lastMonth = new \DateTime();
        $lastMonth->modify('-1 month');
        $matchesUserMax = $this->getContainer()->getParameter('matches_user_maximum_number');

        $users = $em->createQuery("SELECT u as user, mu,
                                      (SELECT COUNT(cmt) FROM ApplicationCommentBundle:Comment cmt WHERE cmt.author = u AND cmt.createdAt >= :lastMonth) as commentCount,
                                      (SELECT COUNT(ss)  FROM AppBundle:SuccessStory ss WHERE ss.user = u AND ss.created >= :lastMonth) as storyCount,
                                      (SELECT COUNT(ug)  FROM AppBundle:UserGoal ug WHERE ug.user = u AND ug.listedDate >= :lastMonth) as goalCount
                                   FROM ApplicationUserBundle:User u
                                   INDEX BY u.id
                                   LEFT JOIN u.matchedUsers mu
                                   WHERE DATE(u.factorCommandDate) < DATE(:currentDate) OR u.factorCommandDate IS NULL
                                   GROUP BY u.id
                                   ORDER BY u.factorCommandDate ASC")
            ->setParameter('currentDate', new \DateTime())
            ->setParameter('lastMonth', $lastMonth)
            ->setMaxResults($input->getArgument('usersCount') ? $input->getArgument('usersCount') : 100)
            ->getResult();

        if (count($users) == 0){
            return;
        }

        $progress = new ProgressBar($output, count($users));
        $progress->start();

        foreach($users as $userId => &$user){

            $stmt = $em->getConnection()
                ->prepare("SELECT u2.id AS u2_id,
                        (
                            (SELECT COUNT(ug1.id)
                             FROM users_goals ug1
                             INNER JOIN users_goals ug2 ON (ug2.goal_id = ug1.goal_id) AND ug2.is_visible = true
                             WHERE (ug1.user_id = u1.id AND ug2.user_id = u2.id) AND ug1.is_visible = true)

                              /

                            (SELECT COUNT(_ug2.id)
                             FROM users_goals _ug2
                             WHERE (_ug2.user_id = u2.id) AND _ug2.is_visible = true)

                        ) AS commonFactor,


                        (
                             SELECT COUNT(ug1_.id)
                             FROM users_goals ug1_
                             INNER JOIN users_goals ug2_ ON (ug2_.goal_id = ug1_.goal_id) AND ug2_.is_visible = true
                             WHERE (ug1_.user_id = u1.id AND ug2_.user_id = u2.id) AND ug1_.is_visible = true

                        ) AS commonGoals


                        FROM fos_user u1
                        INNER JOIN fos_user u2 ON (u2.id <> u1.id)
                        WHERE u1.id = :userId
                        HAVING commonFactor > 0.4 AND commonGoals > 3
                        ORDER BY commonFactor DESC, commonGoals DESC");

            $stmt->bindValue('userId', $userId);
            $stmt->execute();
            $commons = $stmt->fetchAll();

            $matchedUsers = $user['user']->getMatchedUsers()->toArray();

            if (count($matchedUsers) > 0) {
                foreach ($commons as $key => &$common) {
                    if (isset($matchedUsers[$common['u2_id']])) {
                        $matchUser = $matchedUsers[$common['u2_id']];
                        $matchUser->setCommonFactor($common['commonFactor']);
                        $matchUser->setCommonCount($common['commonGoals']);
                        unset($matchedUsers[$common['u2_id']]);
                        unset($commons[$key]);
                    }
                }

                foreach($matchedUsers as $matchedUser){
                    $em->remove($matchedUser);
                }
            }

            foreach($commons as &$common){

                $stmt = $em->getConnection()
                    ->prepare("INSERT INTO match_user (user_id, match_user_id, common_factor, common_count)
                               VALUES (:user_id, :match_user_id, :common_factor, :common_count)");

                $stmt->bindValue('user_id', $userId);
                $stmt->bindValue('match_user_id', $common['u2_id']);
                $stmt->bindValue('common_factor', $common['commonFactor']);
                $stmt->bindValue('common_count', $common['commonGoals']);
                $stmt->execute();
            }

            $user['user']->setActiveFactor(5 * $user['storyCount'] + 2 * $user['commentCount'] + 0.01 * $user['goalCount']);
            $user['user']->setFactorCommandDate(new \DateTime());

            $progress->advance();

            // remove old match users
            $stmt = $em->getConnection()
                ->prepare("DELETE FROM match_user WHERE id IN (select id from (select id
                                           FROM match_user
                                           WHERE match_user.user_id = :userId
                                           ORDER BY common_factor DESC
                                          LIMIT $matchesUserMax, 10000) x)");

            $stmt->bindValue('userId', $userId);
            $stmt->execute();

        }

        $em->flush();

        $progress->finish();
        $output->writeln('success');
    }

}