<?php

namespace WMC\SwiftmailerTwigBundle\Mailer;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Swift_Mailer;
use Swift_Message;

class FOSUserMailer implements MailerInterface
{
    protected $mailer;
    protected $router;
    protected $helper;
    protected $parameters;

    public function __construct(Swift_Mailer $mailer, UrlGeneratorInterface $router, TwigSwiftHelper $helper, array $parameters)
    {
        $this->mailer     = $mailer;
        $this->router     = $router;
        $this->helper     = $helper;
        $this->parameters = $parameters;
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['confirmation'];
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
        $context = array(
            'user' => $user,
            'confirmationUrl' => $url
        );

        $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['resetting'];
        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), true);
        $context = array(
            'user' => $user,
            'confirmationUrl' => $url
        );
        $this->sendMessage($template, $context, $this->parameters['from_email']['resetting'], $user->getEmail());
    }

    /**
     * @param string $templateName
     * @param array  $context
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $message = Swift_Message::newInstance()
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        $this->helper->populateMessage($message, $templateName, $context);
        $this->mailer->send($message);
    }
}
