<?php
namespace Application\UserBundle\DataFixtures\ORM;

use Application\UserBundle\Entity\Badge;
use Application\UserBundle\Entity\Notification;
use Application\UserBundle\Entity\UserNotification;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Application\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadBadgeData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $user1 = $this->getReference('user1');
        $user2 = $this->getReference('user2');
        $user3 = $this->getReference('user3');
        $user4 = $this->getReference('user4');
        $user5 = $this->getReference('user5');
        $user6 = $this->getReference('user6');
        $user7 = $this->getReference('user7');
        $user8 = $this->getReference('user8');
        $user9 = $this->getReference('user9');
        $user10 = $this->getReference('user10');

        $badge1 = new Badge();
        $badge1->setScore(11);
        $badge1->setType(Badge::TYPE_INNOVATOR);
        $badge1->setUser($user1);
        $manager->persist($badge1);

        $badge2 = new Badge();
        $badge2->setScore(15);
        $badge2->setType(Badge::TYPE_MOTIVATOR);
        $badge2->setUser($user1);
        $manager->persist($badge2);

        $badge3 = new Badge();
        $badge3->setScore(21);
        $badge3->setType(Badge::TYPE_TRAVELLER);
        $badge3->setUser($user1);
        $manager->persist($badge3);

        $badge4 = new Badge();
        $badge4->setScore(15);
        $badge4->setType(Badge::TYPE_INNOVATOR);
        $badge4->setUser($user2);
        $manager->persist($badge4);

        $badge5 = new Badge();
        $badge5->setScore(19);
        $badge5->setType(Badge::TYPE_INNOVATOR);
        $badge5->setUser($user3);
        $manager->persist($badge5);

        $badge6 = new Badge();
        $badge6->setScore(13);
        $badge6->setType(Badge::TYPE_INNOVATOR);
        $badge6->setUser($user4);
        $manager->persist($badge6);

        $badge7 = new Badge();
        $badge7->setScore(9);
        $badge7->setType(Badge::TYPE_INNOVATOR);
        $badge7->setUser($user5);
        $manager->persist($badge7);

        $badge8 = new Badge();
        $badge8->setScore(12);
        $badge8->setType(Badge::TYPE_INNOVATOR);
        $badge8->setUser($user6);
        $manager->persist($badge8);

        $badge9 = new Badge();
        $badge9->setScore(17);
        $badge9->setType(Badge::TYPE_INNOVATOR);
        $badge9->setUser($user7);
        $manager->persist($badge9);

        $badge10 = new Badge();
        $badge10->setScore(14);
        $badge10->setType(Badge::TYPE_INNOVATOR);
        $badge10->setUser($user8);
        $manager->persist($badge10);

        $badge11 = new Badge();
        $badge11->setScore(16);
        $badge11->setType(Badge::TYPE_INNOVATOR);
        $badge11->setUser($user9);
        $manager->persist($badge11);

        $badge12 = new Badge();
        $badge12->setScore(7);
        $badge12->setType(Badge::TYPE_INNOVATOR);
        $badge12->setUser($user10);
        $manager->persist($badge12);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 6; // the order in which fixtures will be loaded
    }
}