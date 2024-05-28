<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 12/28/15
 * Time: 5:14 PM
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RandomUiCommand extends ContainerAwareCommand
{
    const USER_LIMIT = 1000;

    protected function configure()
    {
        $this
            ->setName('bl:set:uid')
            ->setDescription('Set random uid ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $begin = 0;
        $usersCount = 0;
        $em = $this->getContainer()->get('doctrine')->getManager();
        $randomService = $this->getContainer()->get('bl_random_id_service');

        do {
            $users = $em->getRepository('ApplicationUserBundle:User')->findByUIdAndLimit(self::USER_LIMIT * $begin++, self::USER_LIMIT);
            if ($users) {
                foreach ($users as $user) {
                    do{
                        $string = $randomService->randomString();
                        $isUser = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('uId' => $string));
                    }
                    while($isUser);

                    $user->setUId($string);
                    $usersCount++;
                }
            }

            $em->flush();

        } while (count($users));

        $output->writeln('set UId  ' . $usersCount . ' users ');
    }
}