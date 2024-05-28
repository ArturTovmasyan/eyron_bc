<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Place;
use AppBundle\Entity\PlaceType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Page;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadPlaceData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        // get users
        $goal1 = $this->getReference('goal14'); //not have userGoal
        $goal2 = $this->getReference('goal2'); //have userGoal without confirm it
        $goal3 = $this->getReference('goal3'); //confirmed goal
        $goal4 = $this->getReference('goal4'); //have userGoal without confirm it
        $goal15 = $this->getReference('goal15'); //have userGoal without confirm it

        //create placeType
        $placeType1 = new PlaceType();
        $placeType1->setName('country');
        $manager->persist($placeType1);

        //create placeType
        $placeType2 = new PlaceType();
        $placeType2->setName('city');
        $manager->persist($placeType2);

        // create place
        $place1 = new Place();
        $place1->setName('Armenia');
        $place1->setPlaceType($placeType1);
        $place1->setMinLatitude(40.0641141);
        $place1->setMaxLatitude(40.2426667);
        $place1->setMinLongitude(44.3620849);
        $place1->setMaxLongitude(44.6140493);
        $manager->persist($place1);

        // create place
        $place2 = new Place();
        $place2->setName('Yerevan');
        $place2->setPlaceType($placeType2);
        $place2->setMinLatitude(40.0641141);
        $place2->setMaxLatitude(40.2426667);
        $place2->setMinLongitude(44.3620849);
        $place2->setMaxLongitude(44.6140493);
        $manager->persist($place2);

        $goal1->addPlace($place1);
        $manager->persist($goal1);

        $goal2->addPlace($place2);
        $manager->persist($goal2);

        $goal3->addPlace($place1);
        $manager->persist($goal3);

        $goal4->addPlace($place2);
        $manager->persist($goal4);

        $goal15->addPlace($place1);
        $manager->persist($goal15);

        $manager->flush();

        $this->addReference('place1', $place1);
        $this->addReference('place2', $place2);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 6; // the order in which fixtures will be loaded
    }
}