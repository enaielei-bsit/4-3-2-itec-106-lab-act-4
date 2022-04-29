<?php
    require_once("./vendor/autoload.php");

    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    } catch (\Throwable $th) {
        //throw $th;
    }

    use Google\Client; 
    use Google\Service\Gmail;

    $accessTokenPath = './token.json';

    function loadToken() {
        global $accessTokenPath;

        if(file_exists($accessTokenPath)) {
            return json_decode(file_get_contents($accessTokenPath), true);
        }

        return null;
    }

    function saveToken($token) {
        if($token == null) return;
        global $accessTokenPath;

        if (!file_exists(dirname($accessTokenPath))) {
            mkdir(dirname($accessTokenPath), 0700, true);
        }
        file_put_contents($accessTokenPath, json_encode($token));
    }

    function deleteToken() {
        global $accessTokenPath, $authUrl;
        if(!file_exists($accessTokenPath)) return false;
        $res = unlink($accessTokenPath);
        $authUrl = null;
        return $res;
    }

    function setAccessToken($token) {
        global $client;
        $client->setAccessToken($token);
        saveToken($token);
    }

    function authenticated() {
        global $client;
        if($client->isAccessTokenExpired()) return null;
        try {
            global $mailer;
            return $mailer->users->getProfile("me");
        } catch (Throwable $e) {
            return null;
        }
    }

    function authenticate() {
        global $client;

        $accessToken = loadToken();
        if($accessToken != null) setAccessToken($accessToken);
        else {
            if(isset($_GET["code"])) {
                $accessToken = $client->fetchAccessTokenWithAuthCode($_GET["code"]);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    return $client->createAuthUrl();
                }
    
                setAccessToken($accessToken);
                header("Location: ./index.php");
            }
        }
    
        if ($client->isAccessTokenExpired()) {
            $refreshToken = $client->getRefreshToken();
            if ($refreshToken) 
                setAccessToken(
                    $client->fetchAccessTokenWithRefreshToken($refreshToken));
            else return $client->createAuthUrl();
        }

        return null;
    }

    $client = new Client();
    // $client->setAuthConfig('./credentials.json');
    $client->setAuthConfig(array(
        "web" => array(
            "client_id" => $_ENV["WEB_CLIENT_ID"],
            "project_id" => $_ENV["WEB_PROJECT_ID"],
            "auth_uri" => $_ENV["WEB_AUTH_URI"],
            "token_uri" => $_ENV["WEB_TOKEN_URI"],
            "auth_provider_x509_cert_url" => $_ENV["WEB_AUTH_PROVIDER_X509_CERT_URL"],
            "client_secret" => $_ENV["WEB_CLIENT_SECRET"],
            "redirect_uris" => [
                $_ENV["WEB_REDIRECT_URIS_0"],
                $_ENV["WEB_REDIRECT_URIS_1"]
            ]
        )
    ));
    $client->setRedirectUri($_ENV["WEB_REDIRECT_URIS_1"]);
    $client->setScopes(Gmail::GMAIL_COMPOSE, Gmail::GMAIL_READONLY);
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    $authUrl = authenticate();
    
    $mailer = $authUrl ? null : new Gmail($client);
?>