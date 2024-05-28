<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 11/2/16
 * Time: 6:36 PM
 */
namespace Application\AffiliateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MergeDuplicateAffiliatesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bl:merge:affiliate')
            ->setDescription('Merge Affiliates with same ufi');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $affiliates = $em->createQuery("SELECT a 
                          FROM ApplicationAffiliateBundle:Affiliate a
                          WHERE (SELECT COUNT(a1.id) FROM ApplicationAffiliateBundle:Affiliate a1 WHERE a1.ufi = a.ufi) > 1
                          ORDER BY a.ufi")
            ->getResult();

        $newAffiliates = [];
        foreach($affiliates as $affiliate){
            if (!isset($newAffiliates[$affiliate->getUfi()])){
                $newAffiliates[$affiliate->getUfi()] = $affiliate;
            }
            else {
                foreach($affiliate->getLinks() as $link){
                    $newAffiliates[$affiliate->getUfi()]->addLink($link);
                    $em->remove($affiliate);
                }
            }
        }

        $em->flush();

        $output->writeln('Success!!');
    }
}