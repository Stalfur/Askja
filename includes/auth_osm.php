<?php
$req_url = 'https://www.openstreetmap.org/oauth/request_token';     // OSM Request Token URL
$authurl = 'https://www.openstreetmap.org/oauth/authorize';         // OSM Authorize URL
$acc_url = 'https://www.openstreetmap.org/oauth/access_token';      // OSM Access Token URL
$api_url = 'http://api.openstreetmap.org/api/0.6/';                // OSM API URL

$conskey = 'vmSBnXI5jWoCDtZYVS19HOUQaeyNMgSGJPzC1wD0';
$conssec = 'b4Pdfnd4mbtYWhG1uAJui0GnA5jnXvBQtWOKQxec';

try {
     $oauth = new OAuth($conskey,$conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
     $request_token_info = $oauth->getRequestToken($req_url);
     session_start();
     $_SESSION['secret'] = $request_token_info['oauth_token_secret'];
     header('Location: '.$authurl."?oauth_token=".$request_token_info['oauth_token']);
} catch(OAuthException $E) {
     print_r($E);
}