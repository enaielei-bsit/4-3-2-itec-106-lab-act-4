<?php
    require_once("./vendor/autoload.php");
    require_once("./client.php");

    use Google\Service\Gmail\Message;
    use Symfony\Component\Mime\Email;

    function sendEmail($to, $subject, $message) {
        global $mailer;
        if($mailer == null) return false;

        if(!is_array($to)) $to = explode(";", $to);
        try {
            $email = (new Email())
                ->from(authenticated()["emailAddress"])
                ->to(...$to)
                ->subject($subject)
                ->text($message);
            $message = new Message();
            $message->setRaw(base64_encode($email->toString()));
            $mailer->users_messages->send("me", $message);
            return true;

        } catch (\Throwable $th) {}

        return false;
    }
?>