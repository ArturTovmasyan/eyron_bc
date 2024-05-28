<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Blog;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadBlogData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $goal1 = $this->getReference('goal1');
        $goal2 = $this->getReference('goal2');

        $blog1 = new Blog();
        $blog1->setTitle('BLOG');
        $blog1->setPublish(1);
        $blog1->setMetaDescription('Description for new blog!!!!!');
        $blog1->setFileName('blogPhoto.jpg');
        $blog1->setFileOriginalName('blogPhoto.jpg');
        $blog1->setPublishedDate(new \DateTime());
        $blog1->setData([['type' => Blog::TYPE_GOAL, 'content' => $goal1->getId()],
                        ['type' => Blog::TYPE_TEXT, 'content' => 'TEXT FOR BLOG DESCRIPTION....']]);

        $manager->persist($blog1);

        $blog2 = new Blog();
        $blog2->setTitle('BLOG1');
        $blog2->setPublish(1);
        $blog2->setMetaDescription('Description for new blog!!!!!');
        $blog2->setFileName('blogPhoto.jpg');
        $blog2->setFileOriginalName('blogPhoto.jpg');
        $blog2->setPublishedDate(new \DateTime());
        $blog2->setData([['type' => Blog::TYPE_GOAL, 'content' => $goal2->getId()],
                        ['type' => Blog::TYPE_TEXT, 'content' => 'TEXT FOR BLOG TEST BLOG DESCRIPTION....']]);

        $manager->persist($blog2);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 7; // the order in which fixtures will be loaded
    }
}