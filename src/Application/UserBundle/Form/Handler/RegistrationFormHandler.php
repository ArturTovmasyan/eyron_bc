<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/2/15
 * Time: 11:23 AM
 */

namespace Application\UserBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseHandler;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class RegistrationFormHandler
 * @package Application\UserBundle\Form\Handler
 */
class RegistrationFormHandler extends BaseHandler
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param FormInterface $form
     * @param UserManagerInterface $userManager
     * @param MailerInterface $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @param $container
     */
    public function __construct(FormInterface $form, RequestStack $requestStack, UserManagerInterface $userManager,
                                MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, Container $container)
    {
        $request = $requestStack->getCurrentRequest();
        // get parent  constructor
        parent::__construct($form, $request, $userManager, $mailer, $tokenGenerator);

        $this->container = $container;

    }

    /**
     * @param UserInterface $user
     * @param bool $confirmation
     */
    protected function onSuccess(UserInterface $user, $confirmation)
    {
        // get bl service
        $blService = $this->container->get('bl_service');

        // upload files
        $blService->uploadFile($user);

        $this->request->getSession()
            ->getFlashBag()
            ->set('userRegistration','User native registration from Web')
        ;

        // generate token
        $token = md5(microtime());

        // set token
        $user->setRegistrationToken($token);

        $this->container->get('bl.email.sender')->sendConfirmEmail($user->getEmail(), $token, $user->getFirstName());

        // Note: if you plan on modifying the user then do it before calling the
        // parent method as the parent method will flush the changes
        parent::onSuccess($user, $confirmation);

        // otherwise add your functionality here
    }
}