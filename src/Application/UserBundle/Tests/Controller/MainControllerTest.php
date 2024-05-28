<?php
///**
// * Created by PhpStorm.
// * User: tigran
// * Date: 2/8/16
// * Time: 6:29 PM
// */
//
//namespace Application\UserBundle\Tests\Controller;
//
//
//use AppBundle\Tests\Controller\BaseClass;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Component\HttpFoundation\Response;
//
//class MainControllerTest extends BaseClass
//{
//
//    /**
//     * This function use to test settingsAction
//     */
//    public function testSettings()
//    {
//
//        $crawler = $this->client->request('GET', '/settings');
//
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, 'can not open goal view page!');
//
//        // Assert that the response content contains a string goal1
//        $this->assertContains('admin', $this->client->getResponse()->getContent(), 'can not find goal1!');
//
//        $email = 'test@test.com';
//        // test form submit
//        $form = $crawler->selectButton('Save')->form(array(
//            'bl_user_settings[currentPassword]' => 'Test1234',
//            'bl_user_settings[plainPassword][first]' => 'Test1234',
//            'bl_user_settings[plainPassword][second]' => 'Test1234',
//            'bl_user_settings[birthDate][month]'=>'2',
//            'bl_user_settings[birthDate][day]'=>'2',
//            'bl_user_settings[birthDate][year]'=>'2016',
//
//        ));
//
//        // submit form
//        $this->client->submit($form);
//
//        if ($profile = $this->client->getProfile()) {
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
//        }
//
//        return $email;
//
//    }
//
//    /**
//     * This function use to test RemoveEmailInSettings
//     * @depends testSettings
//     */
//    public function testRemoveEmailInSettings($email)
//    {
//        $this->client->request('GET', '/settings/remove-email/' . $email);
//
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_FOUND, 'can not open goal view page!');
//
//        if ($profile = $this->client->getProfile()) {
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
//        }
//
//    }
//
//    /**
//     * This function use to test activationUserEmailsAction
//     * @depends testSettings
//     */
//    public function testActivationUserEmails()
//    {
//
//        $user =  $this->em->getRepository('ApplicationUserBundle:User')->findOneByUsername('user@user.com');
//
//        $emails = $user->getUserEmails();
//
//        $email = $emails['test1@test.ru']['userEmails'];
//        $emailToken = $emails['test1@test.ru']['token'];
//
//        $url = "/activation-email/{$emailToken}/{$email}";
//
//        $this->clientSecond->request('GET', $url);
//
//        $this->assertEquals($this->clientSecond->getResponse()->getStatusCode(), Response::HTTP_FOUND, 'can not open goal view page!');
//
//        if ($profile = $this->clientSecond->getProfile()) {
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
//        }
//
//        return $user->getRegistrationToken();
//    }
//
//    /**
//     * This function use to test confirmAction
//     * @depends testActivationUserEmails
//     */
//    public function testConfirm($token)
//    {
//
//        $crawler = $this->clientSecond->request('GET', '/registration-confirm/' . $token);
//
//        $this->assertEquals($this->clientSecond->getResponse()->getStatusCode(), Response::HTTP_OK, 'can not open goal view page!');
//
//        if ($profile = $this->clientSecond->getProfile()) {
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
//        }
//    }
//
//    /**
//     * This function use to test checkLoginAction
//     */
//    public function testCheckLogin()
//    {
//        $this->client->request('GET', '/check-login');
//
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_FOUND, 'can not open goal view page!');
//
//        if ($profile = $this->client->getProfile()) {
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
//        }
//    }
//
//    /**
//     * This function use to test resendMessageAction
//     */
//    public function testResendMessage()
//    {
//        $this->client->request('GET', '/resend-message');
//
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, 'can not open goal view page!');
//
//        $this->assertContains('admin', $this->client->getResponse()->getContent(), 'can not find goal1!');
//
//        if ($profile = $this->client->getProfile()) {
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
//        }
//    }
//
//    /**
//     * This function use to test updateEmailAction
//     */
//    public function testUpdateEmail()
//    {
//        $crawler = $this->client->request('GET', '/update-email');
//
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, 'can not open goal view page!');
//
//        $email = 'admin11@admin.com';
//        // test form submit
//        $form = $crawler->selectButton('Done')->form(array(
//            'form[email]' => $email
//        ));
//
//        // submit form
//        $this->client->submit($form);
//
//        // Assert that the response content contains a string goal1
//
//        if ($profile = $this->client->getProfile()) {
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
//        }
//    }
//
//    /**
//     * This function use to test updateEmailAction
//     */
//    public function testUpdateEmail2()
//    {
//        $crawler = $this->client2->request('GET', '/update-email');
//
//        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_OK, 'can not open goal view page!');
//
//        $email = 'admin@admin.com';
//        // test form submit
//        $form = $crawler->selectButton('Done')->form(array(
//            'form[email]' => $email
//        ));
//
//        // submit form
//        $this->client2->submit($form);
//        // Assert that the response content contains a string goal1
//
//        if ($profile = $this->client2->getProfile()) {
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
//        }
//    }
//}