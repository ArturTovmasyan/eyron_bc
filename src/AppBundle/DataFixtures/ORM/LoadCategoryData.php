<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Category;
use AppBundle\Entity\Tag;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCategoryData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

        $category1 = new Category();
        $category1->setTitle('Travel');
        $category1->setFileOriginalName('ios.png');
        $category1->setFileName('481cf3efac0ca2ec0835e82d55f28d58.png');
        $manager->persist($category1);

        $category2 = new Category();
        $category2->setTitle('Adventure');
        $category2->setFileOriginalName('ios.png');
        $category2->setFileName('481cf3efac0ca2ec0835e82d55f28d58.png');
        $manager->persist($category2);

        $category3 = new Category();
        $category3->setTitle('Experience');
        $category3->setFileOriginalName('ios.png');
        $category3->setFileName('481cf3efac0ca2ec0835e82d55f28d58.png');
        $manager->persist($category3);

        $category4 = new Category();
        $category4->setTitle('New Skills');
        $category4->setFileOriginalName('ios.png');
        $category4->setFileName('481cf3efac0ca2ec0835e82d55f28d58.png');
        $manager->persist($category4);

        $category5 = new Category();
        $category5->setTitle('Wellness');
        $category5->setFileOriginalName('ios.png');
        $category5->setFileName('481cf3efac0ca2ec0835e82d55f28d58.png');
        $manager->persist($category5);

        $category6 = new Category();
        $category6->setTitle('Social');
        $category6->setFileOriginalName('ios.png');
        $category6->setFileName('481cf3efac0ca2ec0835e82d55f28d58.png');
        $manager->persist($category6);

        $category7 = new Category();
        $category7->setTitle('Personal');
        $category7->setFileOriginalName('ios.png');
        $category7->setFileName('481cf3efac0ca2ec0835e82d55f28d58.png');
        $manager->persist($category7);

        $category8 = new Category();
        $category8->setTitle('Most Popular');
        $category8->setFileOriginalName('ios.png');
        $category8->setFileName('481cf3efac0ca2ec0835e82d55f28d58.png');
        $manager->persist($category8);


        $tag1 = new Tag();
        $tag1->setTag('adventure');
        $tag1->addCategory($category1);
        $tag1->addCategory($category2);
        $manager->persist($tag1);

        $tag2 = new Tag();
        $tag2->setTag('travel');
        $tag2->addCategory($category1);
        $manager->persist($tag2);

        $tag3 = new Tag();
        $tag3->setTag('experience');
        $tag3->addCategory($category3);
        $manager->persist($tag3);

        $tag4 = new Tag();
        $tag4->setTag('newskills');
        $tag4->addCategory($category4);
        $manager->persist($tag3);

        $tag5 = new Tag();
        $tag5->setTag('wellness');
        $tag5->addCategory($category5);
        $manager->persist($tag3);

        $tag6 = new Tag();
        $tag6->setTag('social');
        $tag6->addCategory($category6);
        $manager->persist($tag3);

        $tag7 = new Tag();
        $tag7->setTag('personal');
        $tag7->addCategory($category7);
        $manager->persist($tag3);

        $manager->flush();


        $this->addReference('tag1', $tag1);
        $this->addReference('tag2', $tag2);
        $this->addReference('tag3', $tag3);
        $this->addReference('tag4', $tag4);
        $this->addReference('tag5', $tag5);
        $this->addReference('tag6', $tag6);
        $this->addReference('tag7', $tag7);
        $this->addReference('category1', $category1);
        $this->addReference('category2', $category2);
        $this->addReference('category3', $category3);
        $this->addReference('category4', $category4);
        $this->addReference('category5', $category5);
        $this->addReference('category6', $category6);
        $this->addReference('category7', $category7);

    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4; // the order in which fixtures will be loaded
    }
}