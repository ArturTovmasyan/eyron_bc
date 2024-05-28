<?php
namespace Application\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Application\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        // create user
        $user = new User();
        $user->setFirstName('admin');
        $user->setLastName('adminyan');
        $user->setEmail('admin@admin.com');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setIsAdmin(true);
        $user->setEnabled(true);
        $user->setPlainPassword('Test1234');
        $user->setDateOfBirth(new \DateTime('now'));
        $user->setRegistrationToken('a4b9e332d75ac0e99b54bf09d2de1gad');
        $user->setCreatedAt(new \DateTime('now'));
        $user->setUserEmails(['admin1@test.ru'=>
                ['userEmails'=>'admin1@test.ru',
                    'token'=>'f1acf697a3a6477ec984b740701475d9',
                    'primary'=>false]]
        );
        $manager->persist($user);

        // create user
        $user1 = new User();
        $user1->setFirstName('user1');
        $user1->setLastName('useryan');
        $user1->setIsAdmin(false);
        $user1->setEmail('user1@user.com');
        $user1->setEnabled(true);
        $user1->setPlainPassword('Test1234');
        $user1->setDateOfBirth(new \DateTime('now'));
        $user1->setRegistrationToken(null);
        $user1->setCreatedAt(new \DateTime('now'));
        $user1->setUserEmails(['test@test.ru'=>
                                    ['userEmails'=>'test@test.ru',
                                        'token'=>'f1acf697a3a6477ec984b740701475d9',
                                        'primary'=>false]]
        );

        $manager->persist($user1);

        // create user
        $user2 = new User();
        $user2->setFirstName('userToo');
        $user2->setLastName('useryan');
        $user2->setIsAdmin(false);
        $user2->setEmail('user2@user.com');
        $user2->setEnabled(true);
        $user2->setPlainPassword('Test1234');
        $user2->setRegistrationToken(null);
        $user2->setCreatedAt(new \DateTime('now'));
        $user2->setUserEmails(['testangular@ang.com'=>
            ['userEmails' => 'testangular@ang.com',
            'token' => null,
            'primary' => false]]);

        $manager->persist($user2);

        // create user
        $user3 = new User();
        $user3->setFirstName('user3');
        $user3->setLastName('user3');
        $user3->setEmail('user@user.com');
        $user3->setIsAdmin(false);
        $user3->setEnabled(true);
        $user3->setPlainPassword('Test1234');
        $user3->setRegistrationToken('a4b9e332d75ac0e99b54bf09d2de1duid');
        $user3->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user3);

        // create user
        $user4 = new User();
        $user4->setFirstName('user4');
        $user4->setLastName('user4');
        $user4->setIsAdmin(false);
        $user4->setEmail('user4@user.com');
        $user4->setEnabled(true);
        $user4->setPlainPassword('Test1234');
        $user4->setRegistrationToken('a4b9e332d75ac0e99b54bf09d2de1duqw');
        $user4->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user4);

        // create user
        $user5 = new User();
        $user5->setFirstName('user5');
        $user5->setLastName('user5');
        $user5->setIsAdmin(false);
        $user5->setEmail('user5@user.com');
        $user5->setEnabled(true);
        $user5->setPlainPassword('Test1234');
        $user5->setRegistrationToken('a4b9e332d75ac0e99b54bf09d2de1dugh');
        $user5->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user5);

        // create user
        $user6 = new User();
        $user6->setFirstName('user6');
        $user6->setLastName('user6');
        $user6->setIsAdmin(false);
        $user6->setEmail('user6@user.com');
        $user6->setEnabled(true);
        $user6->setPlainPassword('Test1234');
        $user6->setRegistrationToken('a4b9e332d75ac0e99b54bf09d2de1dkuh');
        $user6->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user6);

        // create user
        $user7 = new User();
        $user7->setFirstName('user7');
        $user7->setLastName('user7');
        $user7->setIsAdmin(false);
        $user7->setEmail('user7@user.com');
        $user7->setEnabled(true);
        $user7->setPlainPassword('Test1234');
        $user7->setRegistrationToken('a4b9e332d75ac0e99b54bf09h3de1dugh');
        $user7->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user7);

        // create user
        $user8 = new User();
        $user8->setFirstName('user8');
        $user8->setLastName('user8');
        $user8->setEmail('user8@user.com');
        $user8->setIsAdmin(false);
        $user8->setEnabled(true);
        $user8->setPlainPassword('Test1234');
        $user8->setRegistrationToken('a4b9e332d75ac0m99b54bf09d2de1dugh');
        $user8->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user8);

        // create user
        $user9 = new User();
        $user9->setFirstName('user9');
        $user9->setLastName('user9');
        $user9->setEmail('user9@user.com');
        $user9->setIsAdmin(false);
        $user9->setEnabled(true);
        $user9->setPlainPassword('Test1234');
        $user9->setRegistrationToken('a4b9e332d75ac0o99b54bf09d2de1dugh');
        $user9->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user9);

        // create user
        $user10 = new User();
        $user10->setFirstName('user10');
        $user10->setLastName('user10');
        $user10->setIsAdmin(false);
        $user10->setEmail('user10@user.com');
        $user10->setEnabled(true);
        $user10->setPlainPassword('Test1234');
        $user10->setRegistrationToken('a4b9e382d75ac0e99b54bf09d2de1dugh');
        $user10->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user10);

        // create user
        $user11 = new User();
        $user11->setFirstName('user11');
        $user11->setLastName('useryan');
        $user11->setIsAdmin(false);
        $user11->setEmail('user11@user.com');
        $user11->setEnabled(true);
        $user11->setPlainPassword('Test1234');
        $user11->setRegistrationToken('a4b9e332d75ac0e99b54bfo9d2de1dugh');
        $user11->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user11);

        // create user
        $user12 = new User();
        $user12->setFirstName('user12');
        $user12->setLastName('useryan');
        $user12->setIsAdmin(false);
        $user12->setEmail('user12@user.com');
        $user12->setEnabled(true);
        $user12->setPlainPassword('Test1234');
        $user12->setRegistrationToken('a4b9e332d75ac0e99b54bfo3d2de1dugh');
        $user12->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user12);
        
        // create user
        $user15 = new User();
        $user15->setFirstName('user15');
        $user15->setLastName('useryan');
        $user15->setIsAdmin(false);
        $user15->setEmail('user15@user.com');
        $user15->setEnabled(true);
        $user15->setUId('777777');
        $user15->setPlainPassword('Test1234');
        $user15->setRegistrationToken('a4b9e332d75ac0e99b54bfp9d2de1dugh');
        $user15->setCreatedAt(new \DateTime('now')
        );

        $manager->persist($user15);
        
        $manager->flush();

        $this->addReference('user', $user);
        $this->addReference('user1', $user1);
        $this->addReference('user2', $user2);
        $this->addReference('user3', $user3);
        $this->addReference('user4', $user4);
        $this->addReference('user5', $user5);
        $this->addReference('user6', $user6);
        $this->addReference('user7', $user7);
        $this->addReference('user8', $user8);
        $this->addReference('user9', $user9);
        $this->addReference('user10', $user10);
        $this->addReference('user11', $user11);
        $this->addReference('user12', $user12);
        $this->addReference('user15', $user15);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}