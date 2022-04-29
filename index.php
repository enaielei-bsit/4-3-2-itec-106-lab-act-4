<?php
    require_once("./vendor/autoload.php");
    require_once("./client.php");
    require_once("./mailer.php");

    if(isset($_REQUEST["sign-out"])) {
        if(deleteToken()) header("Location: ./");
    }

    $sent = null;
    if(!$authUrl && isset($_REQUEST["submit"])) {
        $email = $_REQUEST["email"];
        $sent = sendEmail($email["to"], $email["subject"], $email["message"]);
    }

    if(!$authUrl) {
        $authenticated = authenticated();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css">
    <title>BSIT 4-3 | ITEC 106 | Laboratory Activity 3 | PHP Contact Form</title>
</head>
<body>
    <div id="main-content">
        <?php if($sent != null) { ?>
            <?php if($sent) { ?>
                <h1>Email has been successfully sent.</h1>
            <?php } else { ?>
                <h1>Something went wrong while sending your email. Make sure that you're following the correct format.</h1>
            <?php } ?>
            <br>
        <?php } ?>
        <?php if(!$authUrl) { ?>
            <a id="sign-out" href="?sign-out">< Sign Out</a>
            <form action="./" method="post">
                <div class="field">
                    <label for="email_from">From</label>
                    <input type="email" id="email_from" value="<?= $authenticated ? $authenticated["emailAddress"] : "" ?>" readonly disabled>
                </div>
                <div class="field">
                    <label for="email_to">To</label>
                    <i class="hint">For multiple recipents, separate each email with a colon (;). (e.g. juan@email.com; cruz@email.com)</i>
                    <input type="email" id="email_to" name="email[to]" required>
                </div>
                <div class="field">
                    <label for="email_subject">Subject</label>
                    <input type="text" id="email_subject" name="email[subject]">
                </div>
                <div class="field">
                    <label for="email_message">Message</label>
                    <textarea name="email[message]" id="email_message" cols="30" rows="10"></textarea required>
                </div>
                <button type="submit" name="submit" value="1" class="submit">Send</button>
            </form>
        <?php } else { ?>
            <h1>You need to allow this app first to start sending emails with Gmail Service.</h1>
            <i class="hint">Note that this app is not fully verified. If Google warned you about this, just ignore it and continue.</i>
            <a href="<?= $authUrl ?>" class="submit">Authorize</a>
        <?php } ?>
    </div>
</body>
</html>