<?php
/**
 * Created by PhpStorm.
 * User: Artur
 * Date: 03/02/16
 * Time: 12:15 PM
 */

namespace Application\UserBundle\Controller\Rest;

use Application\UserBundle\Entity\User;
use Application\UserBundle\Entity\UserNotify;
use Application\UserBundle\Form\ChangePasswordMobileType;
use Application\UserBundle\Form\SettingsAngularType;
use Application\UserBundle\Form\SettingsMobileType;
use Application\UserBundle\Form\Type\UserNotifyAngularType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\RouteResource("Settings")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class SettingsController extends FOSRestController
{

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to set settings data",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request",
     *         401="Unauthorized user",
     *     },
     * parameters={
     *      {"name"="bl_mobile_user_settings[file]", "dataType"="file", "required"=false, "description"="Users profile image file"},
     *      {"name"="bl_mobile_user_settings[firstName]", "dataType"="string", "required"=true, "description"="User`s first name | min=3 / max=20 symbols"},
     *      {"name"="bl_mobile_user_settings[lastName]", "dataType"="string", "required"=true, "description"="User`s last name | min=3 / max=20 symbols"},
     *      {"name"="bl_mobile_user_settings[primary]", "dataType"="email", "required"=false, "description"="User`s primary email"},
     *      {"name"="bl_mobile_user_settings[addEmail]", "dataType"="email", "required"=false, "description"="Add email for user"},
     *      {"name"="bl_mobile_user_settings[birthDate]", "dataType"="string", "required"=false, "description"="User`s birthday | in this 2015/01/22 format"},
     *      {"name"="bl_mobile_user_settings[language]", "dataType"="string", "required"=false, "description"="User`s language | en|ru"},
     *      {"name"="bl_mobile_user_settings[isCommentNotify]", "dataType"="boolean", "required"=false, "description"="User`s comment email notify | 0|1"},
     *      {"name"="bl_mobile_user_settings[isSuccessStoryNotify]", "dataType"="boolean", "required"=false, "description"="User`s success story email notify | 0|1"},
     *      {"name"="bl_mobile_user_settings[isCommentPushNote]", "dataType"="boolean", "required"=false, "description"="User`s comment push note | 0|1"},
     *      {"name"="bl_mobile_user_settings[isSuccessStoryPushNote]", "dataType"="boolean", "required"=false, "description"="User`s success story push note | 0|1"},
     *      {"name"="bl_mobile_user_settings[isProgressPushNote]", "dataType"="boolean", "required"=false, "description"="User`s progress push note | 0|1"},
     * }
     * )
     * @Rest\View(serializerGroups={"user"})
     * @Secure("ROLE_USER")
     */
    public function postSettingsAction(Request $request)
    {
        //get current user
        $user = $this->getUser();

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // create goal form
        $form = $this->createForm(SettingsMobileType::class, $user);

        // get data from request
        $form->handleRequest($request);

        //check if from valid
        if ($form->isValid()) {

            //get uploadFile service for load profile pictures
            $this->container->get('bl_service')->uploadFile($user);

            $em->persist($user);
            $em->flush();

            $em->getRepository("AppBundle:Goal")->findMyDraftsAndFriendsCount($user);

            return $user;

        }
        else{

            //get form errors
            $formErrors = $form->getErrors(true);

            //set default array
            $returnResult = array();

            foreach($formErrors as $formError)
            {
                //get error field name
                $name = $formError->getOrigin()->getConfig()->getName();

                //set for errors in array
                $returnResult[$name] = $formError->getMessage();
            }

            return new JsonResponse($returnResult, Response::HTTP_BAD_REQUEST);

        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to change user password",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request",
     *         401="Not authorized user",
     *     },
     * parameters={
     *      {"name"="bl_mobile_change_password[currentPassword]", "dataType"="password", "required"=true, "description"="User current password"},
     *      {"name"="bl_mobile_change_password[plainPassword][first]", "dataType"="string", "required"=true, "description"="User new password"},
     *      {"name"="bl_mobile_change_password[plainPassword][second]", "dataType"="string", "required"=true, "description"="User new password"},
     * }
     * )
     * @param $request
     * @return Response
     * @Rest\View()
     * @Secure("ROLE_USER")
     */
    public function postChangePasswordAction(Request $request)
    {
        //get current user
        $user = $this->getUser();

        // create goal form
        $form = $this->createForm(ChangePasswordMobileType::class, $user);

        // get data from request
        $form->handleRequest($request);

        //check if from valid
        if ($form->isValid()) {

            //get fos user manager
            $fosManager = $this->container->get("fos_user.user_manager");

            //update user
            $fosManager->updateUser($user);

            return new Response('', Response::HTTP_OK);
        }
        else{

            //get form errors
            $formErrors = $form->getErrors(true);

            //set default array
            $returnResult = array();

            foreach($formErrors as $formError)
            {
                //get error field name
                $name = $formError->getOrigin()->getConfig()->getName();

                //set for errors in array
                $returnResult[$name] = $formError->getMessage();
            }

            return new JsonResponse($returnResult, Response::HTTP_BAD_REQUEST);

        }
    }


    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to get setting by user id",
     *  statusCodes={
     *         200="OK",
     *     },
     * )
     * @Rest\View(serializerGroups={"settings"})
     * @Secure("ROLE_USER")
     */
    public function getUserFromSettingsAction()
    {
        //get current user
        $user = $this->getUser();

        return $user;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to remove user emails in settings",
     *  statusCodes={
     *         200="Return when successful",
     *         400="Bad request",
     *         401="Unauthorized user",
     *     },
     *  parameters={
     *      {"name"="email", "dataType"="string", "required"=true, "description"="User`s email"},
     * }
     * )
     * @Rest\View(serializerGroups={"user", "completed_profile"})
     * @Secure("ROLE_USER")
     */
    public function deleteEmailAction(Request $request)
    {
        // get all data
        $data = $request->request->all();

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->getUser();

        //get email in request data
        $email = array_key_exists('email', $data) ? $data['email'] : null;

        if(!$email) {
            $email = $request->query->get('email');
        }

        //check if email is empty
        if (!$email) {

            // return 404 if email is empty
            return new Response( 'Email data is empty', Response::HTTP_BAD_REQUEST);
        }

        //get user all emails
        $userEmails = $user->getUserEmails();

        //check if current user have userEmails
        if ($userEmails) {

            //check if email exist in userEmails
            if(!array_key_exists($email, $userEmails)) {

                // return 404 if email is empty
                return new Response('This user not have current email', Response::HTTP_BAD_REQUEST);
            }

            //remove email
            unset($userEmails[$email]);

            //set changed email data
            $user->setUserEmails($userEmails);

            $em->persist($user);
            $em->flush();

           return $user;
        }

        // return 404 if email is empty
        return new Response('User not have removable email', Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\Post("/user/update")
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to set settings data",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request",
     *         401="Unauthorized user",
     *     },
     * parameters={
     *      {"name"="bl_user_angular_settings[firstName]", "dataType"="string", "required"=true, "description"="User`s first name | min=3 / max=20 symbols"},
     *      {"name"="bl_user_angular_settings[lastName]", "dataType"="string", "required"=true, "description"="User`s last name | min=3 / max=20 symbols"},
     *      {"name"="bl_user_angular_settings[addEmail]", "dataType"="email", "required"=false, "description"="Add email for user"},
     *      {"name"="bl_user_angular_settings[email]", "dataType"="email", "required"=false, "description"="User email"},
     *      {"name"="bl_user_angular_settings[birthDate]", "dataType"="string", "required"=false, "description"="User`s birthday | in this 2015/01/22 format"},
     *      {"name"="bl_user_angular_settings[language]", "dataType"="string", "required"=false, "description"="User`s language | en|ru"},
     *      {"name"="bl_user_angular_settings[currentPassword]", "dataType"="password", "required"=false, "description"="User current password"},
     *      {"name"="bl_user_angular_settings[plainPassword][first]", "dataType"="string", "required"=false, "description"="User new password"},
     *      {"name"="bl_user_angular_settings[plainPassword][second]", "dataType"="string", "required"=false, "description"="User new password"},
     * }
     * )
     * @Rest\View(serializerGroups={"user", "completed_profile"})
     * @Secure("ROLE_USER")
     */
    public function postSettingsForAngularAction(Request $request)
    {
        //check if request content type is json
        if ($request->getContentType() == 'application/json' || $request->getContentType() == 'json') {

            //get content and add it in request after json decode
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        //get form data about email value
        $formData = $request->request->get('bl_user_angular_settings');
        $primaryEmail = $formData['primary'];
        $addEmail = $formData['addEmail'];

        //get current user
        $user = $this->getUser();

        $currentEmail = $user->getEmail();

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // create goal form
        $form = $this->createForm(SettingsAngularType::class, $user);

        //set default array
        $returnResult = [];

        //check if primary email equal current email
        if ($primaryEmail != null && $primaryEmail == $currentEmail) {

            //set primary email
            $primaryEmail = null;
        }
        else {

            try {
                //set for check user duplicate error
                $user->setEmail($primaryEmail);
                $em->flush();

            } catch (\Exception $e) {
                $returnResult[] = $e->getMessage();
            }
        }

        //set primary value in entity
        $user->primary = $primaryEmail;
        $user->addEmail = $addEmail;

        // get data from request
        $form->handleRequest($request);

        //check if from valid
        if ($form->isValid()) {

            $em->persist($user);
            $em->flush();

            $liipManager = $this->get('liip_imagine.cache.manager');
            // get drafts
            $em->getRepository("AppBundle:Goal")->findMyIdeasCount($user);
            $goalFriendsCount = 0;
            $em->getRepository("AppBundle:Goal")->findRandomGoalFriends($user->getId(), null, $goalFriendsCount, true);
            $user->setGoalFriendsCount($goalFriendsCount);

            $states = $user->getStats();

            $states['created'] = $em->getRepository('AppBundle:Goal')->findOwnedGoalsCount($user->getId(), false);

            $user->setStats($states);

            if($user->getImagePath()){
                $user->setCachedImage($liipManager->getBrowserPath($user->getImagePath(), 'user_image'));
            }

            return $user;

        } else{

            //get form errors
            $formErrors = $form->getErrors(true);

            foreach($formErrors as $formError)
            {
                //get error field name
                $name = $formError->getOrigin()->getConfig()->getName();

                //set for errors in array
                $returnResult[$name] = $formError->getMessage();
            }

            return new JsonResponse($returnResult, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\Post("/notify-settings/update")
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to set notify settings data",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request",
     *         401="Unauthorized user",
     *     },
     * parameters={
     *      {"name"="bl_user_notify_angular_type[isCommentOnGoalNotify]", "dataType"="string", "required"=false, "description"="User isCommentOnGoalNotify value"},
     *      {"name"="bl_user_notify_angular_type[isCommentOnIdeaNotify]", "dataType"="string", "required"=false, "description"="User isCommentOnGoalNotify value"},
     *      {"name"="bl_user_notify_angular_type[isSuccessStoryOnGoalNotify]", "dataType"="email", "required"=false, "description"="User isCommentOnGoalNotify value"},
     *      {"name"="bl_user_notify_angular_type[isSuccessStoryOnIdeaNotify]", "dataType"="email", "required"=false, "description"="User isCommentOnGoalNotify value"},
     *      {"name"="bl_user_notify_angular_type[isSuccessStoryLikeNotify]", "dataType"="string", "required"=false, "description"="User isCommentOnGoalNotify value"},
     *      {"name"="bl_user_notify_angular_type[isGoalPublishNotify]", "dataType"="string", "required"=false, "description"="User isCommentOnGoalNotify value"},
     *      {"name"="bl_user_notify_angular_type[isCommentReplyNotify]", "dataType"="boolean", "required"=false, "description"="User isCommentOnGoalNotify value"},
     *      {"name"="bl_user_notify_angular_type[isDeadlineExpNotify]", "dataType"="boolean", "required"=false, "description"="User isCommentOnGoalNotify value"},
     *      {"name"="bl_user_notify_angular_type[isNewGoalFriendNotify]", "dataType"="boolean", "required"=false, "description"="User isCommentOnGoalNotify value1"},
     *      {"name"="bl_user_notify_angular_type[isNewIdeaNotify]", "dataType"="boolean", "required"=false, "description"="User isCommentOnGoalNotify value"},
     * }
     * )
     * @Rest\View()
     * @Secure("ROLE_USER")
     */
    public function postEditNotifyAction(Request $request)
    {
        //check if request content type is json
        if ($request->getContentType() == 'application/json' || $request->getContentType() == 'json') {

            //get content and add it in request after json decode
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->getUser();

        //get user notify
        $userNotify = $user->getUserNotifySettings();

        if(!$userNotify) {
            $userNotify = new UserNotify();
        }

        // create goal form
        $form = $this->createForm(UserNotifyAngularType::class, $userNotify);

        // get data from request
        $form->handleRequest($request);

        //check if from valid
        if ($form->isValid()) {

            //get uploadFile service for load profile pictures
//            $this->container->get('bl_service')->uploadFile($user);

            $user->setUserNotifySettings($userNotify);
            $em->persist($user);
            $em->flush();

            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }
        else{

            //get form errors
            $formErrors = $form->getErrors(true);

            //set default array
            $returnResult = array();

            foreach($formErrors as $formError)
            {
                //get error field name
                $name = $formError->getOrigin()->getConfig()->getName();

                //set for errors in array
                $returnResult[$name] = $formError->getMessage();
            }

            return new JsonResponse($returnResult, Response::HTTP_BAD_REQUEST);

        }
    }

    /**
     * @Rest\Get("/user/notify-settings")
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to get user notify settings",
     *  statusCodes={
     *         200="OK",
     *     },
     * )
     * @Rest\View(serializerGroups={"settings"})
     * @Secure("ROLE_USER")
     */
    public function getUserNotifySettingsAction()
    {
        //get current user
        $user = $this->getUser();

        $notifySettings = $user->getUserNotifySettings();

        return $notifySettings;
    }

    /**
     * @Rest\Get("/user/notify-settings/switch-off")
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to switch off all user notification settings",
     *  statusCodes={
     *         200="OK",
     *     },
     * )
     * @Secure("ROLE_USER")
     */
    public function getUserNotifySettingsSwitchOffAction()
    {
        //get current user
        $user = $this->getUser();

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get user notify
        $userNotify = $user->getUserNotifySettings();

        if(!$userNotify) {
            $userNotify = new UserNotify();
        }

        $userNotify->notifySwitchesOff();

        $user->setUserNotifySettings($userNotify);
        $em->persist($user);
        $em->flush();

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }
}