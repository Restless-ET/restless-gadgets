<?php
require 'openid.php';
//Light open id
$openid = new LightOpenID;
if (isset($_GET['login'])) {
    $openid->identity = 'https://www.google.com/accounts/o8/id';
    //setting call back url
    $openid->returnUrl = "http://demo.lookmywebpage.com/google/openid/return.php";
    //finding open id end point from google
    $endpoint = $openid->discover('https://www.google.com/accounts/o8/id');
    $fields =
            '?openid.ns=' . urlencode('http://specs.openid.net/auth/2.0') .
            '&openid.realm=' . urlencode('http://demo.lookmywebpage.com') .
            '&openid.return_to=' . urlencode($openid->returnUrl) .
            '&openid.claimed_id=' . urlencode('http://specs.openid.net/auth/2.0/identifier_select') .
            '&openid.identity=' . urlencode('http://specs.openid.net/auth/2.0/identifier_select') .
            '&openid.mode=' . urlencode('checkid_setup') .
            '&openid.ns.ax=' . urlencode('http://openid.net/srv/ax/1.0') .
            '&openid.ax.mode=' . urlencode('fetch_request') .
            '&openid.ax.required=' . urlencode('email,firstname,lastname') .
            '&openid.ax.type.firstname=' . urlencode('http://axschema.org/namePerson/first') .
            '&openid.ax.type.lastname=' . urlencode('http://axschema.org/namePerson/last') .
            '&openid.ax.type.email=' . urlencode('http://axschema.org/contact/email');
    header('Location: ' . $endpoint . $fields);
}
?>
<html>
    <head>
        <title>Demo Authenticate Users Using Google</title>
    </head>
    <body style="font-family: tahoma; font-size: 12px;">
        <a href="http://lookmywebpage.com/api/google/authenticate-users-using-google-open-id/">Visit Article</a><hr>
        <form action="?login" method="post">
            <button>Login with Google</button>
        </form>        
    </body>
</html>
