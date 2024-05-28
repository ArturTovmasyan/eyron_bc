<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\UserPlace;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserPlaceData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $user1 = $this->getReference('user1'); //have userGoal without confirm it
        $place1 = $this->getReference('place1');

        // create place
        $userPlace1 = new UserPlace();
        $userPlace1->setLatitude(40.17941);
        $userPlace1->setLongitude(44.54084);
        $userPlace1->setUser($user1);
        $userPlace1->setPlace($place1);
        $manager->persist($userPlace1);

        $manager->flush();

        $this->addReference('userPlace1', $userPlace1);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 7; // the order in which fixtures will be loaded
    }
}