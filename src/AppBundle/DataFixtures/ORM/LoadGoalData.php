<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Aphorism;
use AppBundle\Entity\GoalImage;
use AppBundle\Entity\NewFeed;
use AppBundle\Entity\StoryImage;
use AppBundle\Entity\SuccessStory;
use AppBundle\Entity\Tag;
use AppBundle\Entity\UserGoal;
use Application\CommentBundle\Entity\Comment;
use Application\CommentBundle\Entity\Thread;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Goal;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadGoalData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $user = $this->getReference('user');
        $user1 = $this->getReference('user1');
        $user3 = $this->getReference('user3');
        $user4 = $this->getReference('user4');
        $user5 = $this->getReference('user5');
        $user6 = $this->getReference('user6');
        $user7 = $this->getReference('user7');
        $user8 = $this->getReference('user8');
        $user9 = $this->getReference('user9');
        $user10 = $this->getReference('user10');
        $user11 = $this->getReference('user11');
        $user12 = $this->getReference('user12');


        $tag = new Tag();
        $tag->setTag('test');
        $manager->persist($tag);

        $aphorism = new Aphorism();
        $aphorism->setAuthor('user1');
        $aphorism->setContent('One must be a fox in order to recognize traps, and a lion to frighten off wolves.');
        $aphorism->addTag($tag);
        $manager->persist($aphorism);

        // create goal
        $goal1 = new Goal();
        $goal1->setDescription('goal1 goal1');
        $goal1->setTitle('goal1');
        $goal1->setStatus(1);
        $goal1->setVideoLink(null);
        $goal1->setAuthor($user);
        $goal1->setReadinessStatus(Goal::TO_PUBLISH);
        $goal1->setPublish(true);
        $manager->persist($goal1);

        // create goal
        $goal2 = new Goal();
        $goal2->setDescription('goal2 goal2');
        $goal2->setTitle('goal2');
        $goal2->setStatus(1);
        $goal2->setVideoLink(null);
        $goal2->setAuthor($user4);
        $goal2->setReadinessStatus(Goal::TO_PUBLISH);
        $goal2->setPublish(true);
        $manager->persist($goal2);

        // create goal
        $goal3 = new Goal();
        $goal3->setDescription('goal3 goal3');
        $goal3->setTitle('goal3');
        $goal3->setStatus(1);
        $goal3->setVideoLink(null);
        $goal3->setAuthor($user4);
        $goal3->setReadinessStatus(Goal::TO_PUBLISH);
        $goal3->setPublish(true);
        $manager->persist($goal3);

        // create goal
        $goal4 = new Goal();
        $goal4->setDescription('goal4 goal4');
        $goal4->setTitle('goal4');
        $goal4->setStatus(1);
        $goal4->setVideoLink(null);
        $goal4->setReadinessStatus(Goal::TO_PUBLISH);
        $goal4->setAuthor($user4);
        $goal4->setPublish(true);
        $goal4->addTag($tag);
        $manager->persist($goal4);

        // create goal
        $goal5 = new Goal();
        $goal5->setDescription('goal5 goal5');
        $goal5->setTitle('goal5');
        $goal5->setStatus(1);
        $goal5->setVideoLink(null);
        $goal5->setAuthor($user4);
        $goal5->setPublish(true);
        $manager->persist($goal5);

        // create goal
        $goal6 = new Goal();
        $goal6->setDescription('goal6 goal6');
        $goal6->setTitle('goal6');
        $goal6->setStatus(1);
        $goal6->setVideoLink(null);
        $goal6->setReadinessStatus(Goal::TO_PUBLISH);
        $goal6->setAuthor($user4);
        $goal6->setPublish(true);
        $manager->persist($goal6);

        // create goal
        $goal7 = new Goal();
        $goal7->setDescription('goal7 goal7');
        $goal7->setTitle('goal7');
        $goal7->setStatus(1);
        $goal7->setVideoLink(null);
        $goal7->setReadinessStatus(Goal::TO_PUBLISH);
        $goal7->setAuthor($user4);
        $goal7->setPublish(false);
        $manager->persist($goal7);

        // create goal
        $goal8 = new Goal();
        $goal8->setDescription("UNIT TEST is a #great way to #enjoy international #destinations 'at' a low cost and with a fair degree of #freedom,
                                Links:The A-Z of backpacking tips Top 10 Backpacking Destinations for 2014");

        $goal8->setTitle('goal8');
        $goal8->setStatus(1);
        $goal8->setVideoLink(null);
        $goal8->setAuthor($user4);
        $goal8->setPublish(true);
        $manager->persist($goal8);

        // create goal
        $goal9 = new Goal();
        $goal9->setDescription('goal9 goal9');
        $goal9->setTitle('goal9');
        $goal9->setStatus(1);
        $goal9->setVideoLink(null);
        $goal9->setReadinessStatus(Goal::TO_PUBLISH);
        $goal9->addTag($tag);

        $goal9->setLat(40.069099);
        $goal9->setLng(45.038189);
        $goal9->setAddress('Armenia');

        $goal9->setAuthor($user4);
        $goal9->setPublish(true);
        $manager->persist($goal9);


        // create goal
        $goal10 = new Goal();
        $goal10->setDescription('goal10 goal10');
        $goal10->setTitle('goal10');
        $goal10->setStatus(1);
        $goal10->setVideoLink(array("https:\/\/www.youtube.com\/watch?v=KUOhpQDDME4"));
        $goal10->setReadinessStatus(Goal::TO_PUBLISH);
        $goal10->addTag($tag);

        $goal10->setLat(40.069099);
        $goal10->setLng(45.038189);
        $goal10->setAddress('Armenia');

        $goal10->setAuthor($user5);
        $goal10->setPublish(true);
        $manager->persist($goal10);

        // create goal
        $goal11 = new Goal();
        $goal11->setDescription('goal11 goal11');
        $goal11->setTitle('goal11');
        $goal11->setStatus(1);
        $goal11->setVideoLink(null);
        $goal11->setReadinessStatus(Goal::TO_PUBLISH);
        $goal11->setAuthor($user4);
        $goal11->setPublish(false);
        $goal11->setVideoLink(array("https:\/\/www.youtube.com\/watch?v=KUOhpQDDME4"));
        $goal11->setLat(40.069099);
        $goal11->setLng(45.038189);
        $goal11->setAddress('Armenian');


        $manager->persist($goal11);


        // create goal
        $goal12 = new Goal();
        $goal12->setDescription('goal12 goal12');
        $goal12->setTitle('goal12');
        $goal12->setStatus(1);
        $goal12->setVideoLink(null);
        $goal12->setReadinessStatus(Goal::DRAFT);
        $goal12->setAuthor($user);
        $goal12->setPublish(false);
        $manager->persist($goal12);

        // create goal
        $goal13 = new Goal();
        $goal13->setDescription('goal13 goal13');
        $goal13->setTitle('goal13');
        $goal13->setStatus(1);
        $goal13->setVideoLink(null);
        $goal13->setReadinessStatus(Goal::TO_PUBLISH);
        $goal13->setAuthor($user11);
        $goal13->setPublish(true);
        $manager->persist($goal13);

        // create goal
        $goal14 = new Goal();
        $goal14->setDescription('goal14 goal14');
        $goal14->setTitle('goal14');
        $goal14->setStatus(1);
        $goal14->setVideoLink(null);
        $goal14->setReadinessStatus(Goal::TO_PUBLISH);
        $goal14->addTag($tag);

        $goal14->setLat(40.069099);
        $goal14->setLng(45.038189);
        $goal14->setAddress('Armenia');

        $goal14->setAuthor($user8);
        $goal14->setPublish(true);
        $manager->persist($goal14);

        // create goal
        $goal15 = new Goal();
        $goal15->setDescription('goal15 goal15');
        $goal15->setTitle('goal15');
        $goal15->setStatus(1);
        $goal15->setVideoLink(null);
        $goal15->setLat(40.17941);
        $goal15->setLng(4.54084);
        $goal15->setReadinessStatus(Goal::TO_PUBLISH);
        $goal15->setAuthor($user1);
        $goal15->setPublish(true);
        $manager->persist($goal15);

        // create goal
        $userGoal32 = new UserGoal();
        $userGoal32->setUser($user1);
        $userGoal32->setGoal($goal15);
        $userGoal32->setIsVisible(true);
        $userGoal32->setNote('32222');
        $userGoal32->setImportant(true);
        $userGoal32->setUrgent(false);
        $userGoal32->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal32);

        // create goal
        $userGoal1 = new UserGoal();
        $userGoal1->setUser($user);
        $userGoal1->setGoal($goal1);
        $userGoal1->setIsVisible(true);
        $userGoal1->setNote('aaaaa');
        $userGoal1->setImportant(true);
        $userGoal1->setUrgent(false);
        $userGoal1->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal1);

        // create goal
        $userGoal2 = new UserGoal();
        $userGoal2->setUser($user);
        $userGoal2->setGoal($goal2);
        $userGoal2->setIsVisible(true);
        $userGoal2->setNote('aaaaa');
        $userGoal2->setImportant(false);
        $userGoal2->setUrgent(true);
        $manager->persist($userGoal2);

        // create goal
        $userGoal3 = new UserGoal();
        $userGoal3->setUser($user1);
        $userGoal3->setGoal($goal3);
        $userGoal3->setIsVisible(true);
        $userGoal3->setNote('sfsdf');
        $userGoal3->setImportant(true);
        $userGoal3->setStatus(UserGoal::COMPLETED);
        $userGoal3->setUrgent(false);
        $userGoal3->setConfirmed(true);
        $manager->persist($userGoal3);

        // create goal
        $userGoal4 = new UserGoal();
        $userGoal4->setUser($user1);
        $userGoal4->setGoal($goal4);
        $userGoal4->setIsVisible(true);
        $userGoal4->setNote('goal4');
        $userGoal4->setImportant(false);
        $userGoal4->setUrgent(true);
        $userGoal4->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal4);

        // create goal
        $userGoal5 = new UserGoal();
        $userGoal5->setUser($user1);
        $userGoal5->setGoal($goal7);
        $userGoal5->setIsVisible(true);
        $userGoal5->setNote('goal4');
        $userGoal5->setImportant(false);
        $userGoal5->setUrgent(false);
        $userGoal5->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal5);

        // create goal
        $userGoal6 = new UserGoal();
        $userGoal6->setUser($user1);
        $userGoal6->setGoal($goal6);
        $userGoal6->setIsVisible(true);
        $userGoal6->setNote('goal6');
        $userGoal6->setImportant(true);
        $userGoal6->setUrgent(true);
        $userGoal6->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal6);


        // create goal
        $userGoal7 = new UserGoal();
        $userGoal7->setUser($user1);
        $userGoal7->setGoal($goal8);
        $userGoal7->setIsVisible(true);
        $userGoal7->setNote('goal8');
        $userGoal7->setImportant(false);
        $userGoal7->setUrgent(true);
        $userGoal7->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal7);

        // create goal
        $userGoal8 = new UserGoal();
        $userGoal8->setUser($user1);
        $userGoal8->setGoal($goal9);
        $userGoal8->setIsVisible(true);
        $userGoal8->setNote('goal9');
        $userGoal8->setImportant(true);
        $userGoal8->setUrgent(true);
        $userGoal8->setIsVisible(true);
//        $userGoal8->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal8);

        // create goal
        $userGoal9 = new UserGoal();
        $userGoal9->setUser($user3);
        $userGoal9->setGoal($goal10);
        $userGoal9->setIsVisible(true);
        $userGoal9->setNote('goal9');
        $userGoal9->setImportant(true);
        $userGoal9->setUrgent(true);
        $userGoal9->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal9);

        // create goal
        $userGoal10 = new UserGoal();
        $userGoal10->setUser($user3);
        $userGoal10->setGoal($goal7);
        $userGoal10->setIsVisible(true);
        $userGoal10->setNote('goal9');
        $userGoal10->setImportant(true);
        $userGoal10->setUrgent(true);
        $userGoal10->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal10);

        // create goal
        $userGoal11 = new UserGoal();
        $userGoal11->setUser($user4);
        $userGoal11->setGoal($goal7);
        $userGoal11->setIsVisible(true);
        $userGoal11->setNote('goal9');
        $userGoal11->setImportant(true);
        $userGoal11->setUrgent(true);
        $userGoal11->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal11);

        // create goal
        $userGoal12 = new UserGoal();
        $userGoal12->setUser($user5);
        $userGoal12->setGoal($goal7);
        $userGoal12->setIsVisible(true);
        $userGoal12->setNote('goal9');
        $userGoal12->setImportant(true);
        $userGoal12->setUrgent(true);
        $userGoal12->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal12);

        // create goal
        $userGoal13 = new UserGoal();
        $userGoal13->setUser($user6);
        $userGoal13->setGoal($goal4);
        $userGoal13->setIsVisible(true);
        $userGoal13->setNote('goal9');
        $userGoal13->setImportant(true);
        $userGoal13->setUrgent(true);
        $userGoal13->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal13);

        // create goal
        $userGoal14 = new UserGoal();
        $userGoal14->setUser($user7);
        $userGoal14->setGoal($goal7);
        $userGoal14->setIsVisible(true);
        $userGoal14->setNote('goal9');
        $userGoal14->setImportant(true);
        $userGoal14->setUrgent(true);
        $userGoal14->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal14);

        // create goal
        $userGoal15 = new UserGoal();
        $userGoal15->setUser($user8);
        $userGoal15->setGoal($goal7);
        $userGoal15->setIsVisible(true);
        $userGoal15->setNote('goal9');
        $userGoal15->setImportant(true);
        $userGoal15->setUrgent(true);
        $userGoal15->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal15);

        // create goal
        $userGoal16 = new UserGoal();
        $userGoal16->setUser($user9);
        $userGoal16->setGoal($goal7);
        $userGoal16->setIsVisible(true);
        $userGoal16->setNote('goal9');
        $userGoal16->setImportant(true);
        $userGoal16->setUrgent(true);
        $userGoal16->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal16);

        // create goal
        $userGoal17 = new UserGoal();
        $userGoal17->setUser($user10);
        $userGoal17->setGoal($goal7);
        $userGoal17->setIsVisible(true);
        $userGoal17->setNote('goal9');
        $userGoal17->setImportant(true);
        $userGoal17->setUrgent(true);
        $userGoal17->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal17);

        // create goal
        $userGoal18 = new UserGoal();
        $userGoal18->setUser($user3);
        $userGoal18->setGoal($goal4);
        $userGoal18->setIsVisible(true);
        $userGoal18->setNote('goal9');
        $userGoal18->setImportant(true);
        $userGoal18->setUrgent(true);
        $userGoal18->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal18);

        // create goal
        $userGoal19 = new UserGoal();
        $userGoal19->setUser($user4);
        $userGoal19->setGoal($goal4);
        $userGoal19->setIsVisible(true);
        $userGoal19->setNote('goal9');
        $userGoal19->setImportant(true);
        $userGoal19->setUrgent(true);
        $userGoal19->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal19);

        // create goal
        $userGoal20 = new UserGoal();
        $userGoal20->setUser($user5);
        $userGoal20->setGoal($goal4);
        $userGoal20->setIsVisible(true);
        $userGoal20->setNote('goal9');
        $userGoal20->setImportant(true);
        $userGoal20->setUrgent(true);
        $userGoal20->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal20);

        // create goal
        $userGoal21 = new UserGoal();
        $userGoal21->setUser($user7);
        $userGoal21->setGoal($goal4);
        $userGoal21->setIsVisible(true);
        $userGoal21->setNote('goal9');
        $userGoal21->setImportant(true);
        $userGoal21->setUrgent(true);
        $userGoal21->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal21);

        // create goal
        $userGoal22 = new UserGoal();
        $userGoal22->setUser($user3);
        $userGoal22->setGoal($goal6);
        $userGoal22->setIsVisible(true);
        $userGoal22->setNote('goal9');
        $userGoal22->setImportant(true);
        $userGoal22->setUrgent(true);
        $userGoal22->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal22);

        // create goal
        $userGoal23 = new UserGoal();
        $userGoal23->setUser($user4);
        $userGoal23->setGoal($goal6);
        $userGoal23->setIsVisible(true);
        $userGoal23->setNote('goal9');
        $userGoal23->setImportant(true);
        $userGoal23->setUrgent(true);
        $userGoal23->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal23);

        // create goal
        $userGoal24 = new UserGoal();
        $userGoal24->setUser($user5);
        $userGoal24->setGoal($goal6);
        $userGoal24->setIsVisible(true);
        $userGoal24->setNote('goal9');
        $userGoal24->setImportant(true);
        $userGoal24->setUrgent(true);
        $userGoal24->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal24);

        // create goal
        $userGoal25 = new UserGoal();
        $userGoal25->setUser($user3);
        $userGoal25->setGoal($goal5);
        $userGoal25->setIsVisible(true);
        $userGoal25->setNote('goal9');
        $userGoal25->setImportant(true);
        $userGoal25->setUrgent(true);
        $userGoal25->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal25);

        // create goal
        $userGoal26 = new UserGoal();
        $userGoal26->setUser($user4);
        $userGoal26->setGoal($goal5);
        $userGoal26->setIsVisible(true);
        $userGoal26->setNote('goal9');
        $userGoal26->setImportant(true);
        $userGoal26->setUrgent(true);
        $userGoal26->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal26);

        // create goal
        $userGoal27 = new UserGoal();
        $userGoal27->setUser($user5);
        $userGoal27->setGoal($goal5);
        $userGoal27->setIsVisible(true);
        $userGoal27->setNote('goal9');
        $userGoal27->setImportant(true);
        $userGoal27->setUrgent(true);
        $userGoal27->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal27);

        // create goal
        $userGoal28 = new UserGoal();
        $userGoal28->setUser($user4);
        $userGoal28->setGoal($goal10);
        $userGoal28->setIsVisible(true);
        $userGoal28->setNote('goal9');
        $userGoal28->setImportant(true);
        $userGoal28->setUrgent(true);
        $userGoal28->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal28);

        // create goal
        $userGoal29 = new UserGoal();
        $userGoal29->setUser($user12);
        $userGoal29->setGoal($goal13);
        $userGoal29->setIsVisible(true);
        $userGoal29->setNote('goal13');
        $userGoal29->setImportant(true);
        $userGoal29->setUrgent(true);
        $userGoal29->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal29);


        // create goal
        $userGoal30 = new UserGoal();
        $userGoal30->setUser($user11);
        $userGoal30->setGoal($goal13);
        $userGoal30->setIsVisible(true);
        $userGoal30->setNote('goal9');
        $userGoal30->setImportant(true);
        $userGoal30->setUrgent(true);
        $userGoal30->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal30);

        // create goal
        $userGoal31 = new UserGoal();
        $userGoal31->setUser($user4);
        $userGoal31->setGoal($goal13);
        $userGoal31->setIsVisible(true);
        $userGoal31->setNote('goal8');
        $userGoal31->setImportant(false);
        $userGoal31->setUrgent(true);
        $userGoal31->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal31);

        // create goal
        $userGoal32 = new UserGoal();
        $userGoal32->setUser($user12);
        $userGoal32->setGoal($goal12);
        $userGoal32->setIsVisible(true);
        $userGoal32->setNote('goal13');
        $userGoal32->setImportant(true);
        $userGoal32->setUrgent(true);
        $userGoal32->setDoDate(new \DateTime('now'));
        $manager->persist($userGoal32);

        $oldPhotoPath = __DIR__ . '/images/leon.jpg';
        $photoPath = __DIR__ . '/../../../../web/uploads/images/photo.jpg';

        // copy photo path
        copy($oldPhotoPath, $photoPath);

        // new uploaded file
        $photo = new UploadedFile(
            $photoPath,
            'photo.jpg',
            'image/jpeg'
        );

        $goalImage = new GoalImage();
        $goalImage->setGoal($goal1);
        $goal1->addImage($goalImage);
        $goalImage->setFile($photo);
        $goalImage->setFileName($photo->getClientOriginalName());
        $goalImage->setFileSize($photo->getSize());
        $goalImage->setFileOriginalName($photo->getFilename());

        $manager->persist($goalImage);


        $oldPhotoPath2 = __DIR__ . '/images/image.jpg';
        $photoPath2 = __DIR__ . '/../../../../web/uploads/images/photo2.jpg';

        // copy photo path
        copy($oldPhotoPath2, $photoPath2);

        // new uploaded file
        $photo2 = new UploadedFile(
            $photoPath2,
            'photo2.jpg',
            'image/jpeg'
        );

        $goalImage2 = new GoalImage();
        $goalImage2->setGoal($goal2);
        $goal2->addImage($goalImage2);
        $goalImage2->setFile($photo2);
        $goalImage2->setFileName($photo2->getClientOriginalName());
        $goalImage2->setFileSize($photo2->getSize());
        $goalImage2->setFileOriginalName($photo2->getFilename());

        $manager->persist($goalImage2);


        $oldPhotoPath3 = __DIR__ . '/images/image1.jpg';
        $photoPath3 = __DIR__ . '/../../../../web/uploads/images/photo3.jpg';

        // copy photo path
        copy($oldPhotoPath3, $photoPath3);

        // new uploaded file
        $photo3 = new UploadedFile(
            $photoPath3,
            'photo3.jpg',
            'image/jpeg'
        );

        $goalImage3 = new GoalImage();
        $goalImage3->setGoal($goal3);
        $goalImage3->setFile($photo3);
        $goal3->addImage($goalImage3);
        $goalImage3->setFileName($photo3->getClientOriginalName());
        $goalImage3->setFileSize($photo3->getSize());
        $goalImage3->setFileOriginalName($photo3->getFilename());

        $manager->persist($goalImage3);

        $oldPhotoPath4 = __DIR__ . '/images/image2.jpg';
        $photoPath4 = __DIR__ . '/../../../../web/uploads/images/photo4.jpg';

        // copy photo path
        copy($oldPhotoPath4, $photoPath4);

        // new uploaded file
        $photo4 = new UploadedFile(
            $photoPath4,
            'photo4.jpg',
            'image/jpeg'
        );

        $goalImage4 = new GoalImage();
        $goalImage4->setGoal($goal4);
        $goalImage4->setFile($photo4);
        $goal4->addImage($goalImage4);
        $goalImage4->setFileName($photo4->getClientOriginalName());
        $goalImage4->setFileSize($photo4->getSize());
        $goalImage4->setFileOriginalName($photo4->getFilename());

        $manager->persist($goalImage4);

        $oldPhotoPath5 = __DIR__ . '/images/image3.jpg';
        $photoPath5 = __DIR__ . '/../../../../web/uploads/images/photo5.jpg';

        // copy photo path
        copy($oldPhotoPath5, $photoPath5);

        // new uploaded file
        $photo5 = new UploadedFile(
            $photoPath5,
            'photo5.jpg',
            'image/jpeg'
        );

        $goalImage5 = new GoalImage();
        $goalImage5->setGoal($goal5);
        $goalImage5->setFile($photo5);
        $goal5->addImage($goalImage5);
        $goalImage5->setFileName($photo5->getClientOriginalName());
        $goalImage5->setFileSize($photo5->getSize());
        $goalImage5->setFileOriginalName($photo5->getFilename());

        $manager->persist($goalImage5);

        $oldPhotoPath6 = __DIR__ . '/images/image4.jpg';
        $photoPath6 = __DIR__ . '/../../../../web/uploads/images/photo6.jpg';

        // copy photo path
        copy($oldPhotoPath6, $photoPath6);

        // new uploaded file
        $photo6 = new UploadedFile(
            $photoPath6,
            'photo6.jpg',
            'image/jpeg'
        );

        $goalImage6 = new GoalImage();
        $goalImage6->setGoal($goal6);
        $goalImage6->setFile($photo6);
        $goal6->addImage($goalImage6);
        $goalImage6->setFileName($photo6->getClientOriginalName());
        $goalImage6->setFileSize($photo6->getSize());
        $goalImage6->setFileOriginalName($photo6->getFilename());

        $manager->persist($goalImage6);


        $oldPhotoPath7 = __DIR__ . '/images/image5.jpg';
        $photoPath7 = __DIR__ . '/../../../../web/uploads/images/photo7.jpg';

        // copy photo path
        copy($oldPhotoPath7, $photoPath7);

        // new uploaded file
        $photo7 = new UploadedFile(
            $photoPath7,
            'photo7.jpg',
            'image/jpeg'
        );

        $goalImage7 = new GoalImage();
        $goalImage7->setGoal($goal6);
        $goalImage7->setFile($photo7);
        $goal7->addImage($goalImage7);
        $goalImage7->setFileName($photo7->getClientOriginalName());
        $goalImage7->setFileSize($photo7->getSize());
        $goalImage7->setFileOriginalName($photo7->getFilename());

        $manager->persist($goalImage7);

        $oldPhotoPath8 = __DIR__ . '/images/image3.jpg';
        $photoPath8 = __DIR__ . '/../../../../web/uploads/images/photo8.jpg';

        // copy photo path
        copy($oldPhotoPath8, $photoPath8);

        // new uploaded file
        $photo8 = new UploadedFile(
            $photoPath8,
            'photo8.jpg',
            'image/jpeg'
        );

        $goalImage8 = new GoalImage();
        $goalImage8->setGoal($goal8);
        $goalImage8->setFile($photo8);
        $goal8->addImage($goalImage8);
        $goalImage8->setFileName($photo8->getClientOriginalName());
        $goalImage8->setFileSize($photo8->getSize());
        $goalImage8->setFileOriginalName($photo8->getFilename());

        $manager->persist($goalImage8);

        $oldPhotoPath9 = __DIR__ . '/images/leon.jpg';
        $photoPath9 = __DIR__ . '/../../../../web/uploads/images/photo9.jpg';

        // copy photo path
        copy($oldPhotoPath9, $photoPath9);

        // new uploaded file
        $photo8 = new UploadedFile(
            $photoPath7,
            'photo9.jpg',
            'image/jpeg'
        );

        $goalImage9 = new GoalImage();
        $goalImage9->setGoal($goal9);
        $goalImage9->setFile($photo8);
        $goal9->addImage($goalImage9);
        $goalImage9->setFileName($photo8->getClientOriginalName());
        $goalImage9->setFileSize($photo8->getSize());
        $goalImage9->setFileOriginalName($photo8->getFilename());

        $manager->persist($goalImage9);

        $oldPhotoPath10 = __DIR__ . '/images/image4.jpg';
        $photoPath10 = __DIR__ . '/../../../../web/uploads/images/photo10.jpg';

        // copy photo path
        copy($oldPhotoPath10, $photoPath10);

        // new uploaded file
        $photo9 = new UploadedFile(
            $photoPath10,
            'photo10.jpg',
            'image/jpeg'
        );

        $goalImage10 = new GoalImage();
        $goalImage10->setGoal($goal9);
        $goalImage10->setFile($photo9);
        $goal9->addImage($goalImage9);
        $goalImage10->setFileName($photo9->getClientOriginalName());
        $goalImage10->setFileSize($photo9->getSize());
        $goalImage10->setFileOriginalName($photo9->getFilename());

        $manager->persist($goalImage10);

        $oldPhotoPath11 = __DIR__ . '/images/image6.jpg';
        $photoPath11 = __DIR__ . '/../../../../web/uploads/images/photo11.jpg';

        // copy photo path
        copy($oldPhotoPath11, $photoPath11);

        // new uploaded file
        $photo10 = new UploadedFile(
            $photoPath11,
            'photo11.jpg',
            'image/jpeg'
        );

        $goalImage11 = new GoalImage();
        $goalImage11->setGoal($goal10);
        $goal10->addImage($goalImage11);
        $goalImage11->setFile($photo10);
        $goalImage11->setFileName($photo10->getClientOriginalName());
        $goalImage11->setFileSize($photo10->getSize());
        $goalImage11->setFileOriginalName($photo10->getFilename());

        $manager->persist($goalImage11);

        $oldPhotoPath13 = __DIR__ . '/images/image6.jpg';
        $photoPath13 = __DIR__ . '/../../../../web/uploads/images/photo13.jpg';

        // copy photo path
        copy($oldPhotoPath13, $photoPath13);

        // new uploaded file
        $photo13 = new UploadedFile(
            $photoPath11,
            'photo13.jpg',
            'image/jpeg'
        );

        $goalImage13 = new GoalImage();
        $goalImage13->setGoal($goal13);
        $goal13->addImage($goalImage13);
        $goalImage13->setFile($photo13);
        $goalImage13->setFileName($photo13->getClientOriginalName());
        $goalImage13->setFileSize($photo13->getSize());
        $goalImage13->setFileOriginalName($photo13->getFilename());

        $manager->persist($goalImage13);

        $manager->flush();

        $thread = new Thread();
        $thread->setId('goal_'.$goal9->getSlug());
        $thread->setNumComments(3);

        $manager->persist($thread);

        $thread1 = new Thread();
        $thread1->setId('goal_'.$goal10->getSlug());
        $thread1->setNumComments(3);

        $manager->persist($thread1);

        $comment = new Comment();
        $comment->setAuthor($user1);
        $comment->setBody("Comment1");
        $comment->setThread($thread);
        $manager->persist($comment);

        $comment = new Comment();
        $comment->setAuthor($user1);
        $comment->setBody("Comment1");
        $comment->setThread($thread);
        $manager->persist($comment);

        $comment = new Comment();
        $comment->setAuthor($user1);
        $comment->setBody("Comment2");
        $comment->setThread($thread);
        $manager->persist($comment);

        $comment = new Comment();
        $comment->setAuthor($user1);
        $comment->setBody("Comment3");
        $comment->setThread($thread);
        $manager->persist($comment);

        $comment = new Comment();
        $comment->setAuthor($user1);
        $comment->setBody("Comment4");
        $comment->setThread($thread);
        $manager->persist($comment);

        $comment = new Comment();
        $comment->setAuthor($user1);
        $comment->setBody("Comment5");
        $comment->setThread($thread);
        $manager->persist($comment);

        $comment = new Comment();
        $comment->setAuthor($user1);
        $comment->setBody("Comment7");
        $comment->setThread($thread1);
        $manager->persist($comment);

        $successStory = new SuccessStory();
        $successStory->setUser($user1);
        $successStory->setVideoLink(array("https:\/\/www.youtube.com\/watch?v=KUOhpQDDME4"));
        $successStory->setStory("STORY");
        $successStory->setGoal($goal11);
        $manager->persist($successStory);

        $successStory1 = new SuccessStory();
        $successStory1->setUser($user1);
        $successStory1->setVideoLink(array("https:\/\/www.youtube.com\/watch?v=KUOhpQDDME4"));
        $successStory1->setStory("STORY10");
        $successStory1->setGoal($goal10);
        $manager->persist($successStory1);

        //create newFeed object for activity json structure test
        $newFeed = new NewFeed(null, null, $goal13);
        $newFeed->setUser($user12);
        $newFeed->setAction(true);
        $newFeed->setDatetime(new \DateTime('now'));
        $newFeed->addGoal($goal13);
        $manager->persist($newFeed);

        //create newFeed object for activity json structure test
        $newFeed1 = new NewFeed(null, null, $goal14);
        $newFeed1->setUser($user12);
        $newFeed1->setAction(true);
        $newFeed1->setDatetime(new \DateTime('now'));
        $newFeed1->addGoal($goal14);
        $manager->persist($newFeed1);

        $manager->flush();

        $this->addReference('goal1', $goal1);
        $this->addReference('goal2', $goal2);
        $this->addReference('goal3', $goal3);
        $this->addReference('goal4', $goal4);
        $this->addReference('goal5', $goal5);
        $this->addReference('goal14', $goal14);
        $this->addReference('goal15', $goal15);
        $this->addReference('userGoal1', $userGoal1);
        $this->addReference('userGoal2', $userGoal2);
        $this->addReference('userGoal3', $userGoal3);
        $this->addReference('userGoal4', $userGoal4);
        $this->addReference('userGoal5', $userGoal5);
        $this->addReference('goalImage', $goalImage);
        $this->addReference('goalImage2', $goalImage2);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}