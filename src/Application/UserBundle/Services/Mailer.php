<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 11/27/15
 * Time: 2:33 PM
 */
namespace Application\UserBundle\Services;

use FOS\UserBundle\Mailer\Mailer as BaseMailer;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class Mailer extends BaseMailer
{
    protected $translator;
    protected $request;
    protected $apiHost;

    public function __construct($translator, $apiHost, $mailer, RouterInterface $router, EngineInterface $templating, array $parameters)
    {
        $this->translator = $translator;
        $this->apiHost = $apiHost;

        parent::__construct($mailer, $router, $templating, $parameters);
    }

    /**
     * @param string $renderedTemplate
     * @param $fromEmail
     * @param string $toEmail
     */
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setContentType("text/html; charset=UTF-8")
            ->setBody($body);

        $this->mailer->send($message);
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['resetting.template'];

        //generate activation token url 
        $url = sprintf('%s/resetting/reset/%s', $this->apiHost, $user->getConfirmationToken());

        $rendered = $this->templating->render($template, [
            'user' => $user,
            'confirmationUrl' => $url
        ]);

        $this->sendEmailMessage($rendered, $this->parameters['from_email']['resetting'], $user->getEmail());
    }
}

