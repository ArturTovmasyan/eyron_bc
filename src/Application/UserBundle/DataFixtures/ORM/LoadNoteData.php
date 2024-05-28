<?php
namespace Application\UserBundle\DataFixtures\ORM;

use Application\UserBundle\Entity\Notification;
use Application\UserBundle\Entity\UserNotification;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Application\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadNoteData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

        $userNot1 = new UserNotification();
        $userNot1->setIsRead(false);
        $userNot1->setUser($user1);
        $manager->persist($userNot1);

        $userNot2 = new UserNotification();
        $userNot2->setIsRead(false);
        $userNot2->setUser($user1);
        $manager->persist($userNot2);

        $userNot3 = new UserNotification();
        $userNot3->setIsRead(false);
        $userNot3->setUser($user1);
        $manager->persist($userNot3);


        // create user
        $note1 = new Notification();
        $note1->setBody('TEST1 NOTE');
        $note1->setGoalId(1);
        $note1->addUserNotification($userNot1);
        $note1->setLink('/goal/goal9');

        $manager->persist($note1);

        $userNot1->setNotification($note1);
        $manager->persist($userNot1);


        // create user
        $note2 = new Notification();
        $note2->setBody('TEST2 NOTE');
        $note2->setGoalId(1);
        $note2->addUserNotification($userNot2);
        $note2->setLink('/goal/goal7');
        $manager->persist($note2);

        $userNot2->setNotification($note2);
        $manager->persist($userNot2);

        // create user
        $note3 = new Notification();
        $note3->setBody('TEST3 NOTE');
        $note3->setGoalId(1);
        $note3->addUserNotification($userNot3);
        $note3->setLink('/goal/goal8');
        $manager->persist($note3);

        $userNot3->setNotification($note3);
        $manager->persist($userNot3);

        $manager->flush();

    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5; // the order in which fixtures will be loaded
    }
}