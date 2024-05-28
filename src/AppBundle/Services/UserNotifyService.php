<?php

namespace AppBundle\Services;

use AppBundle\Entity\Email;
use AppBundle\Entity\Goal;
use Application\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;


/**
 * Class UserNotifyService
 * @package AppBundle\Services
 */
class UserNotifyService extends AbstractProcessService
{
    // constants for notify type
    const USER_ACTIVITY = 'user_activity';
    const COMMENT_GOAL = 'comment_goal';
    const COMMENT_IDEA = 'comment_idea';
    const SUCCESS_STORY_GOAL = 'success_story_goal';
    const SUCCESS_STORY_IDEA = 'success_story_idea';
    const SUCCESS_STORY_LIKE = 'success_story_like';
    const PUBLISH_GOAL = 'publish_goal';
    const REPLY_COMMENT = 'reply_comment';
    const DEADLINE = 'deadline';
    const NEW_GOAL_FRIEND = 'new_goalfriend';
    const NEW_IDEA = 'new_idea';

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected  $container;


    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $receiverId
     * @param $type
     * @param array $options
     * @param bool $byPool
     * @return string|void
     */
    public function prepareAndSendNotifyViaProcess($receiverId, $type, array $options = [], $byPool = false)
    {
        //get user notify value in parameter
        $enabledByConfig = $this->container->getParameter('user_notify');

        //check if user notify is disabled
        if(!$enabledByConfig) {
            return;
        }

        // get doctrine manager
        $em = $this->container->get('doctrine')->getManager();

        // get receiver user
        $receiver = $em->getRepository('ApplicationUserBundle:User')->find($receiverId);

        // check receiver
        if(!$receiver){
            return;
        }
        $subjectTransId = 'email_body_for_' . $type;
        $bodyTransId = 'email_body_for_' . $type;

        //get receiver language
        $language = $receiver->getLanguage() ? $receiver->getLanguage() : 'en';

        $object = null;
        $router = $this->container->get('router');
        $viewLink = null;
        $goal = null;
        $sender = null;

        // check type
        switch ($type){
            case self::USER_ACTIVITY:
                $subject = $this->container->get('translator')->trans($subjectTransId, array(), 'email', $language);
                $body = $this->container->get('translator')->trans($bodyTransId, array(), 'email', $language);
                break;
            case self::COMMENT_GOAL:
            case self::COMMENT_IDEA:
                if(array_key_exists('goalId', $options) && array_key_exists('commentId', $options)) {
                    $goalId = $options['goalId'];
                    $commentId = $options['commentId'];
                    $goal = $em->getRepository('AppBundle:Goal')->find($goalId);
                    $comment = $em->getRepository('ApplicationCommentBundle:Comment')->find($commentId);

                    // check goal
                    if($goal && $comment){
                        $sender = $comment->getAuthor();
                        $viewLink = $router->generate('inner_goal', array('slug' => $goal->getSlug()), true);
                        $prepareData = array('%goalName%' => $goal->getTitle(), '%senderName%' => $sender->showName());
                        $subject = $this->container->get('translator')->trans($subjectTransId, $prepareData, 'email', $language);
                        $body = $this->container->get('translator')->trans($bodyTransId, $prepareData, 'email', $language);
                    }
                }
            break;
                break;
            case self::SUCCESS_STORY_GOAL:
            case self::SUCCESS_STORY_IDEA:
                if(array_key_exists('successStoryId', $options)) {
                    $successStoryId = $options['successStoryId'];
                    $successStory = $em->getRepository('AppBundle:SuccessStory')->find($successStoryId);

                    // check goal
                    if($successStory){
                        $sender = $successStory->getUser();
                        $goal =  $successStory->getGoal();
                        $viewLink = $router->generate('inner_goal', array('slug' => $goal->getSlug()), true);
                        $prepareData = array('%goalName%' => $goal->getTitle(), '%senderName%' => $sender->showName());
                        $subject = $this->container->get('translator')->trans($subjectTransId, $prepareData, 'email', $language);
                        $body = $this->container->get('translator')->trans($bodyTransId, $prepareData, 'email', $language);
                    }
                }
                break;
            case self::SUCCESS_STORY_LIKE:
                if(array_key_exists('goalId', $options) && array_key_exists('senderId', $options)) {
                    $goalId = $options['goalId'];
                    $senderId = $options['senderId'];
                    $goal = $em->getRepository('AppBundle:Goal')->find($goalId);
                    $sender = $em->getRepository('ApplicationUserBundle:User')->find($senderId);

                    // check goal
                    if($goal && $sender){
                        $viewLink = $router->generate('inner_goal', array('slug' => $goal->getSlug()), true);
                        $anchor = array_key_exists('anchor', $options) ? $options['anchor'] : '#';
                        $viewLink .= $anchor;
                        $prepareData = array('%goalName%' => $goal->getTitle(), '%senderName%' => $sender->showName());
                        $subject = $this->container->get('translator')->trans($subjectTransId, $prepareData, 'email', $language);
                        $body = $this->container->get('translator')->trans($bodyTransId, $prepareData, 'email', $language);
                    }
                }
                break;
            case self::PUBLISH_GOAL:
            case self::DEADLINE:
                if(array_key_exists('goalId', $options)) {
                    $goalId = $options['goalId'];
                    $goal = $em->getRepository('AppBundle:Goal')->find($goalId);
                    // check goal
                    if($goal){
                        $viewLink = $router->generate('inner_goal', array('slug' => $goal->getSlug()), true);
                        $prepareData = array('%goalName%' => $goal->getTitle());
                        $subject = $this->container->get('translator')->trans($subjectTransId, $prepareData, 'email', $language);
                        $body = $this->container->get('translator')->trans($bodyTransId, $prepareData, 'email', $language);
                    }
                }
                break;
            case self::REPLY_COMMENT:
                    if(array_key_exists('goalId', $options)) {
                        $goalId = $options['goalId'];
                        $goal = $em->getRepository('AppBundle:Goal')->find($goalId);

                        // check goal
                        if($goal){
                            $viewLink = $router->generate('inner_goal', array('slug' => $goal->getSlug()), true);
                            $anchor = array_key_exists('anchor', $options) ? $options['anchor'] : '#';
                            $viewLink .= $anchor;
                            $subject = $this->container->get('translator')->trans($subjectTransId, [], 'email', $language);
                            $body = $this->container->get('translator')->trans($bodyTransId, [], 'email', $language);
                        }
                    }
                break;
            case self::NEW_GOAL_FRIEND:
                if(array_key_exists('count', $options)) {
                    $count = $options['count'];
                    $viewLink = $router->generate('goal_friends', array(), true);
                    $viewLink .= '#/recently';
                    $prepareData = array('%count%' => $count);
                    $subject = $this->container->get('translator')->trans($subjectTransId, $prepareData, 'email', $language);
                    $body = $this->container->get('translator')->trans($bodyTransId, $prepareData, 'email', $language);
                }
                break;
            case self::NEW_IDEA:
                if(array_key_exists('count', $options)){
                    $count = $options['count'];
                    $viewLink = $router->generate('goals_list', array(), true);
                    $parameters = array('%count%' => $count);
                    $subject = $this->container->get('translator')->trans($subjectTransId, $parameters, 'email', $language);
                    $body = $this->container->get('translator')->trans($bodyTransId, $parameters, 'email', $language);
                }

                break;
        }

        // check texts
        if(!isset($subject) || !isset($body)){
            return;
        }

        // check and send email
        if($receiver->mustEmailNotify($type)){

            $contentParameters =  array(
                'goal' => $goal,
                'body' => $body,
                'object' => $object,
                'receiver' => $receiver,
                'sender' => $sender,
                'viewLink' => $viewLink,
                'language' => $language,
                'userEmail' => $router->generate('edit_user_notify', array(), true)
            );

            $id = $this->createEmailRecorder($receiver, $subject, $contentParameters);
            $emailRecordLink =  $viewLink = $router->generate('open-email',
                array('id' => $id), true);

            $contentParameters['openEmailLink'] = $emailRecordLink;

            //generate content for email
            $content = $this->container->get('templating')->render(
                'AppBundle:Templates:userNotifyEmail.html.twig',
                $contentParameters
            );

            //get receiver email
            $email = $receiver->getEmail();

            if($byPool){
                $this->sendEmailByPool($email, $content, $subject);
            }
            else{
                $this->sendEmail($email, $content, $subject);
            }
        }

        // check and send push notify
        if($receiver->mustPushedNotify($type)){
            //get put notification service
            $sendNoteService = $this->container->get('bl_put_notification_service');

            //send notification to mobile
            $sendNoteService->sendPushNote($receiver, $subject);
        }
        return;
    }

    /**
     * @param User $receiver
     * @param $title
     * @param $contentParameters
     * @return int
     */
    public function createEmailRecorder(User $receiver, $title, $contentParameters)
    {
        $em = $this->container->get('doctrine')->getManager();

        //generate content for email
        $content = $this->container->get('templating')->render(
            'AppBundle:Templates:userNotifyEmailForDB.html.twig',
            $contentParameters
        );

        $email = new Email();
        $email->setUser($receiver);
        $email->setTitle($title);
        $email->setContent($content);
        $em->persist($email);
        $em->flush();

        return $email->getId();
    }


    /**
     * @param $users
     * @return int
     * @throws \Throwable
     */
    public function uniqueDevice($users)
    {
        $em = $this->container->get('doctrine')->getManager();
        $androidIds = array();
        $iosIds = array();
        $androidRelations = [];
        $iosRelations = [];
        $count = 0;

        foreach($users as $k => $user)
        {
            // check registration ids
            $registrations = $user->getRegistrationIds();
            
            // check registrations
            if($registrations){
                // check, and get android ids, if exist
                if(array_key_exists( PutNotificationService::ANDROID, $registrations)){
                    $androidIds = $registrations[PutNotificationService::ANDROID];
                    foreach($androidIds as $i => $id){
                        $notSet = false;
                        $key = array_search($id, $androidRelations);

                        if(!($key === false)){
                            $count++;
                            if(gettype ($key) == "integer"){
                                $firstUser = $users[$key];
                                $secondUser = $users[$k];
                                if($firstUser->getCreatedAt() < $secondUser->getCreatedAt()){
                                    unset($androidRelations[$key]);
                                    $registrationIds = $users[$key]->getRegistrationIds();
                                    unset($registrationIds[PutNotificationService::ANDROID][0]);
                                    $users[$key]->setRegistrationIds($registrationIds);
                                } else{
                                    $notSet = true;
                                    $registrationIds = $users[$k]->getRegistrationIds();
                                    unset($registrationIds[PutNotificationService::ANDROID][$i]);
                                    $users[$k]->setRegistrationIds($registrationIds);
                                }
                            } else{
                                $keys = explode("_", $key);
                                $firstUser = $users[$keys[0]];
                                $secondUser = $users[$k];
                                if($firstUser->getCreatedAt() < $secondUser->getCreatedAt()){
                                    unset($androidRelations[$key]);
                                    $registrationIds = $users[$keys[0]]->getRegistrationIds();
                                    unset($registrationIds[PutNotificationService::ANDROID][$keys[1]]);
                                    $users[$keys[0]]->setRegistrationIds($registrationIds);
                                } else{
                                    $notSet = true;
                                    $registrationIds = $users[$k]->getRegistrationIds();
                                    unset($registrationIds[PutNotificationService::ANDROID][$i]);
                                    $users[$k]->setRegistrationIds($registrationIds);
                                }
                            };
                        }
                        if(!$notSet) {
                            if (!$i) {
                                $androidRelations[$k] = $id;
                            } else {
                                $androidRelations[$k . '_' . $i] = $id;
                            }
                        }
                    }

                }
                // check, and get ios ids, if exist
                if(array_key_exists(PutNotificationService::IOS, $registrations)){
                    $iosIds = $registrations[PutNotificationService::IOS];
                    foreach($iosIds as $i => $id){
                        $notSet = false;
                        $key = array_search($id, $iosRelations);
                        if(!($key === false)){
                            $count++;
                            if(gettype ($key) == "integer"){
                                $firstUser = $users[$key];
                                $secondUser = $users[$k];
                                if($firstUser->getCreatedAt() < $secondUser->getCreatedAt()){
                                    unset($iosRelations[$key]);
                                    $registrationIds = $firstUser->getRegistrationIds();
                                    unset($registrationIds[PutNotificationService::IOS][0]);
                                    $users[$key]->setRegistrationIds($registrationIds);
                                } else{
                                    $notSet = true;
                                    $registrationIds = $secondUser->getRegistrationIds();
                                    unset($registrationIds[PutNotificationService::IOS][$i]);
                                    $users[$k]->setRegistrationIds($registrationIds);
                                }
                            } else{
                                $keys = explode("_", $key);
                                $firstUser = $users[$keys[0]];
                                $secondUser = $users[$k];
                                if($firstUser->getCreatedAt() < $secondUser->getCreatedAt()){
                                    unset($iosRelations[$key]);
                                    $registrationIds = $firstUser->getRegistrationIds();
                                    unset($registrationIds[PutNotificationService::IOS][$keys[1]]);
                                    $users[$keys[0]]->setRegistrationIds($registrationIds);
                                } else{
                                    $notSet = true;
                                    $registrationIds = $secondUser->getRegistrationIds();
                                    unset($registrationIds[PutNotificationService::IOS][$i]);
                                    $users[$k]->setRegistrationIds($registrationIds);
                                }
                            };
                        }
                        if(!$notSet){
                            if(!$i){
                                $iosRelations[$k] = $id;
                            } else{
                                $iosRelations[$k . '_' . $i] = $id;
                            }
                        }

                    }

                }
            }
        }
        
        return $count;
    }

    /**
     * @param User $receiver
     * @param $type
     * @param array $options
     */
    public function sendNotifyToUser(User $receiver, $type, array $options = [])
    {
        //get user notify value in parameter
        $enabledByConfig = $this->container->getParameter('user_notify');

        //check if user notify is disabled
        if(!$enabledByConfig ) {
            return;
        }

        // json data for array
        $options = "'" . json_encode($options) . "'";

        $this->runAsProcess('user_notify', 'prepareAndSendNotifyViaProcess',
            array($receiver->getId(), $type, $options));
    }


    /**
     * This function is used to send notify about new comment
     * @deprecated
     * @param Goal $goal
     * @param User $user
     * @param $commentText
     * @throws \Swift_TransportException
     */
    public function sendNotifyAboutNewComment(Goal $goal, User $user, $commentText)
    {
        //get user notify value in parameter
        $enabledByConfig = $this->container->getParameter('user_notify');

        //get put notification service
        $sendNoteService = $this->container->get('bl_put_notification_service');

        //get kernel debug
        $notProd = $this->container->getParameter('kernel.debug');

        //get goal author
        $author = $goal->getAuthor();

        //get comment notify settings value
        $enabled = $author->getIsCommentNotify();

        //check if user notify is disabled
        if(!$enabledByConfig || $notProd || !$enabled) {
            return;
        }

        //check if comment text is null
        if(!$commentText) {

            //get entity manager
            $em = $this->container->get('doctrine')->getManager();

            //get last comment
            $lastComment = $em->getRepository('ApplicationCommentBundle:Comment')->findLastCommentByGoalId($goal->getId());

            //get comment text
            $commentText = $lastComment['body'];
        }

        //get author email
        $email = $author->getEmail();

        //get author language
        $language = $author->getLanguage() ? $author->getLanguage() : 'en';

        //get sender name
        $userName = $user->showName();

        //get subject for email
        $subject = $this->container->get('translator')->trans('subject_for_comment_email', array('%senderName%' => $userName), 'email', $language);

        //send notification to mobile
        $sendNoteService->sendPushNote($user, $subject);

        //generate content for email
        $content = $this->container->get('templating')->render(
            'AppBundle:Main:userNotifyEmail.html.twig',
            array('eventText' => $commentText, 'goal' => $goal, 'type' => 'comments', 'user' => $user, 'mailText' => 'notify_comment', 'language' => $language)
        );
        
        $this->sendEmail($email, $content, $subject);
    }

    /**
     * @deprecated
     * This function is used to send notify about new success story
     *
     * @param Goal $goal
     * @param User $user
     * @param $storyText
     * @throws \Swift_TransportException
     */
    public function sendNotifyAboutNewSuccessStory(Goal $goal, User $user, $storyText)
    {
        //get user notify value in parameter
        $enabledByConfig = $this->container->getParameter('user_notify');

        //get put notification service
        $sendNoteService = $this->container->get('bl_put_notification_service');
        
        //get kernel debug
        $notProd = $this->container->getParameter('kernel.debug');

        //get goal author
        $author = $goal->getAuthor();

        //get story notify settings value
        $enabled = $author->getIsSuccessStoryNotify();

        //check if user notify is disabled
        if(!$enabledByConfig || $notProd || !$enabled) {
            return;
        }

        //get author email
        $email = $author->getEmail();

        //get author language
        $language = $author->getLanguage() ? $author->getLanguage() : 'en';
        
        //get sender name
        $userName = $user->showName();

        //get subject for email
        $subject = $this->container->get('translator')->trans('subject_for_story_email', array('%senderName%' => $userName), 'email', $language);

        //send notification to mobile
        $sendNoteService->sendPushNote($user, $subject);

        //generate content for email
        $content = $this->container->get('templating')->render(
            'AppBundle:Main:userNotifyEmail.html.twig',
            array('eventText' => $storyText, 'goal' => $goal, 'type' => 'success_story', 'user' => $user, 'mailText' => 'notify_success_story', 'language' => $language)
        );

        $this->sendEmail($email, $content, $subject);
    }

    /**
     * @param $email
     * @param $content
     * @param $subject
     * @throws \Exception
     * @throws \Swift_TransportException
     */
    public function sendEmail($email, $content, $subject)
    {
        //get no-reply email
        $noReplyEmail = $this->container->getParameter('no_reply');

        //get project name
        $projectName = $this->container->getParameter('email_sender');

        try {
            //calculate message
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($noReplyEmail, $projectName)
                ->setTo($email)
                ->setContentType('text/html; charset=UTF-8')
                ->setBody($content, 'text/html');

            //send email
            $this->container->get('mailer')->send($message);

        } catch(\Swift_TransportException $e) {

            throw $e;
        }
    }


    /**
     * @param $email
     * @param $content
     * @param $subject
     */
    public function sendEmailByPool($email, $content, $subject)
    {
        //get no-reply email
        $noReplyEmail = $this->container->getParameter('no_reply');

        //get project name
        $projectName = $this->container->getParameter('email_sender');
        $spool = new \Swift_FileSpool($this->container->get('kernel')->getRootDir(). "/spool");
        $transporter = new \Swift_SpoolTransport($spool);
        $mailer = new \Swift_Mailer($transporter);
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($noReplyEmail, $projectName)
            ->setTo($email)
            ->setContentType('text/html; charset=UTF-8')
            ->setBody($content, 'text/html');

        $mailer->send($message);
    }

}