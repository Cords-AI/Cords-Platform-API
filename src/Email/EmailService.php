<?php

namespace App\Email;

use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private Environment $twig
    ) {
    }

    public function send(string $to, string $subject, string $template, $vm): void
    {
        if (empty($_ENV["SENDGRID_KEY"])) {
            return;
        }
        $sendGridTransport = new SendgridSmtpTransport($_ENV["SENDGRID_KEY"]);
        $mailer = new Mailer($sendGridTransport);
        $html = $this->twig->render("$template.html.twig", ['vm' => $vm]);
        $fromEmail = $_ENV['FROM_EMAIL'];
        $email = (new Email())
            ->to($to)
            ->from($fromEmail)
            ->subject($subject)
            ->html($html);

        $mailer->send($email);
    }
}
