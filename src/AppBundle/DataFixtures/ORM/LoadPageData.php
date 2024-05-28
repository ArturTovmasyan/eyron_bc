<?php
namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Page;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadPageData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        // create page
        $page = new Page();
        $page->setDescription('page page');
        $page->setName('page');
        $page->setSlug('page');
        $page->setTitle('About us');
        $page->setPosition(1);
        $manager->persist($page);

        $page2 = new Page();
        $page2->setDescription('<p>Contact us</p>');
        $page2->setName('Contact Us');
        $page2->setSlug('contact-us');
        $page2->setTitle('Contacts');
        $page2->setPosition(3);
        $manager->persist($page2);

        $manager->flush();

        $this->addReference('page', $page);
        $this->addReference('page2', $page2);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3; // the order in which fixtures will be loaded
    }
}