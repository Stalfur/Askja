<?php

/*
 * Functions from Zverik
 * Level0 editor
 * https://github.com/Zverik/Level0
 */
function oauth_login() {
	global $error;
	try {
		$oauth = new OAuth(CLIENT_ID,CLIENT_SECRET,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
		$request_token_info = $oauth->getRequestToken(OSM_OAUTH_URL.'request_token');
		$_SESSION['secret'] = $request_token_info['oauth_token_secret'];
		header('Location: '.OSM_OAUTH_URL."authorize?oauth_token=".$request_token_info['oauth_token']);
		exit;
	} catch(OAuthException $E) {
		$error = 'OAuth error '.$E->getCode().': '.$E->getMessage();
	}
}

function oauth_logout() {
	unset($_SESSION['osm_user']);
	unset($_SESSION['osm_langs']);
	unset($_SESSION['osm_token']);
	unset($_SESSION['osm_secret']);
}

function oauth_callback() {
	global $php_self;

	if(!isset($_GET['oauth_token'])) {
		echo "Error! There is no OAuth token!";
	} elseif(!isset($_SESSION['secret'])) {
		echo "Error! There is no OAuth secret!";
	} else {
		try {
			$oauth = new OAuth(CLIENT_ID, CLIENT_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
			$oauth->setToken($_GET['oauth_token'], $_SESSION['secret']);
			$access_token_info = $oauth->getAccessToken(OSM_OAUTH_URL.'access_token');
			unset($_SESSION['secret']);

			$_SESSION['osm_token'] = strval($access_token_info['oauth_token']);
			$_SESSION['osm_secret'] = strval($access_token_info['oauth_token_secret']);
			$oauth->setToken($_SESSION['osm_token'], $_SESSION['osm_secret']);

			try {
				$oauth->fetch(OSM_API_URL.'user/details');
				$user_details = $oauth->getLastResponse();

				$xml = simplexml_load_string($user_details);       
				$_SESSION['osm_user'] = strval($xml->user['display_name']);

				$langs = array();
				foreach( $xml->user->languages->lang as $lang )
					$langs[] = strval($lang);
				$_SESSION['osm_langs'] = $langs;
			} catch(OAuthException $E) {
				// well, we don't need that
			}

			header("Location: ".$php_self.'?action=remember');
		} catch(OAuthException $E) {
			echo("<pre>Exception:\n");
			print_r($E);
			echo '</pre>';
		}
	}
	exit;
}
