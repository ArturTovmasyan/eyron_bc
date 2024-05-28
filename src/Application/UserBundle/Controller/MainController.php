<?php

namespace Application\UserBundle\Controller;

use Application\UserBundle\Entity\User;
use Application\UserBundle\Form\SettingsType;
use Application\UserBundle\Form\UserNotifySettingsType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MainController extends Controller
{
    /**
     * This function is used to remove user emails by email name
     *
     * @Route("/settings/remove-email/{email}", name="remove_email")
     * @Secure(roles="ROLE_USER")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeEmailInSettings(Request $request, $email)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get last url for redirect
        $lastUrl = $request->headers->get('referer') ? $request->headers->get('referer') : $this->generateUrl('homepage');

        //get current user
        $user = $this->getUser();

        //get user all emails
        $userEmails = $user->getUserEmails();

        //check if current user have userEmails
        if ($userEmails) {

            //remove email in userEmails
            unset($userEmails[$email]);

            //set changed email data
            $user->setUserEmails($userEmails);

            $em->persist($user);
            $em->flush();
        }

        return $this->redirect($lastUrl);

    }

    /**
     * @Route("/activation-email/{emailToken}/{email}", name="activation_user_email")
     * @Secure(roles="ROLE_USER")
     */
    public function activationUserEmailsAction(Request $request, $emailToken, $email)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->getUser();

        //check if user not exist
        if (!$user) {
            // return Exception
            throw $this->createNotFoundException("User not found");
        }

        //get user emails
        $userEmails = $user->getUserEmails();

        //get current email data
        $data = $userEmails[$email];

        //get userEmail value in array
        $currentEmailToken = $data['token'];

        //check if tokens is equal
        if ($currentEmailToken == $emailToken) {

            //set token null in userEmails by key
            $userEmails[$email]['token'] = null;

            //set activation email token null
            $user->setUserEmails($userEmails);

            if ($user->getSocialFakeEmail() == $user->getEmail()){
                $user->primary = $email;
            }
        }
        else {
            // return Exception
            throw $this->createNotFoundException("Invalid email token for this user");
        }

            $em->persist($user);
            $em->flush($user);

        return $this->redirectToRoute('homepage');

    }

    /**
     * @Route("/check-login", name="check-login")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function checkLoginAction(Request $request)
    {
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/registration-confirm/{token}", name="registration_confirm")
     * @Template()
     * @param $token
     * @return array
     */
    public function confirmAction($token)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("ApplicationUserBundle:User")->findOneBy(array('registrationToken' => $token));

        // check user
        if (!$user) {
            throw new NotFoundHttpException('Invalid token');
        }

        $user->setRegistrationToken(null);
        $em->persist($user);
        $em->flush();

        return array();
    }

    /**
     * @Route("/resend-message", name="resend_message")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @return RedirectResponse
     */
    public function resendMessageAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->getRegistrationToken()) {
            $this->get('bl.email.sender')->sendConfirmEmail($user->getEmail(), $user->getRegistrationToken(), $user->getFirstName());

            return array();
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            return new RedirectResponse($referer);
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/update-email", name="update_email")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateEmailAction(Request $request)
    {
        if (!$this->getUser()->getRegistrationToken()){
            return $this->redirectToRoute('homepage');
        }

        $user = new User();
        $form = $this->createFormBuilder($user, array('validation_groups' => array('update_email')))
            ->add('email', 'email', array('attr' => array(
                'oninvalid' => "EmailValidation(this)",
                'oninput' => "EmailValidation(this)"
            )
            ))
            ->add('done', 'submit')
            ->getForm();


        if ($request->getMethod() == "POST"){

            $form->handleRequest($request);

            if ($form->isValid()){

                $token = md5(microtime());
                $email = $form->get('email')->getData();

                $this->getUser()->setRegistrationToken($token);
                $this->getUser()->setEmail($email);

                $this->get('bl.email.sender')->sendConfirmEmail($email, $token, $this->getUser()->getFirstName());

                $em = $this->getDoctrine()->getManager();
                $em->flush();

                return $this->redirectToRoute('homepage');
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/edit/notification", name="edit_user_notify")
     * @Security("has_role('ROLE_USER')")
     * @Template()
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function userNotifyEditAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->getUser();

        // create goal form
        $form = $this->createForm(UserNotifySettingsType::class, $user);

        // check request method
        if ($request->isMethod('POST')) {

            // get data from request
            $form->handleRequest($request);

            // check valid
            if($form->isValid()){

                //get uploadFile service
                $this->get('bl_service')->uploadFile($user);

                $em->persist($user);
                $em->flush();
                
                return $this->redirectToRoute('edit_user_notify');
            }
        }

        //This part is used for profile completion percent calculation
        if ($this->getUser()->getProfileCompletedPercent() != 100) {
            $em->getRepository("ApplicationUserBundle:User")->updatePercentStatuses($this->getUser());
        }

        $em->getRepository('ApplicationUserBundle:User')->setUserStats($this->getUser());


        return array('form' => $form->createView(), 'profileUser' => $user);
    }

    /**
     * @Route("/edit/profile", name="edit_user_profile")
     * @Security("has_role('ROLE_USER')")
     * @Template("ApplicationUserBundle:Main:profileEdit.html.twig")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @param Request $request
     */
    public function profileEditAction(Request $request)
    {
        $user = $this->getUser();
        $currentEmail = $user->getEmail();

        $form = $this->createForm(SettingsType::class, $user);

        // check request method
        if ($request->isMethod('POST')) {

            //get form data in request
            $formData = $request->request->get('bl_user_settings');

            // get data from request
            $form->handleRequest($request);

            //get primary email
            $primaryEmail = $request->request->get('primary');

            //check if primary email equal current email
            if ($primaryEmail != null && $primaryEmail == $currentEmail) {

                //set primary email
                $primaryEmail = null;
            }
            else {

                //set for check user duplicate error
                $user->setEmail($primaryEmail);
            }

            //get validator
            $validator = $this->get('validator');

            //get errors
            $errors = $validator->validate($user, null, array('Settings'));

            //check count of errors
            if (count($errors) == 0) {

                //set current email
                $user->setEmail($currentEmail);

                if ($currentEmail == $user->getSocialFakeEmail() && $formData['addEmail']){
                    $user->setEmail($formData['addEmail']);
                    $formData['addEmail'] = "";
                    $request->request->set('bl_user_settings', $formData);
                }
            }

            //get fos user manager
            $fosManager = $this->container->get('fos_user.user_manager');

            //check if form is valid
            if ($form->isValid() && count($errors) == 0) {

                //set primary value in entity
                $user->primary = $primaryEmail;

                //set updated for preUpdate event
                $user->setUpdatedAt(new \DateTime());

                //get uploadFile service
                $this->get('bl_service')->uploadFile($user);

                //update user
                $fosManager->updateUser($user);

                return $this->redirectToRoute('edit_user_profile');
            }
            else {
                $fosManager->reloadUser($user);
            }
        }

        $em = $this->getDoctrine()->getManager();

        //This part is used for profile completion percent calculation
        if ($this->getUser()->getProfileCompletedPercent() != 100) {
            $em->getRepository("ApplicationUserBundle:User")->updatePercentStatuses($this->getUser());
        }

        $em->getRepository('ApplicationUserBundle:User')->setUserStats($this->getUser());


        return array('form' => $form->createView(), 'profileUser' => $user);
    }
}
