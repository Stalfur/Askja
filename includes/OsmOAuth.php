<?php
/*
 * Accessing OpenStreetMap API with OAuth authentication
 *
 * The parent class EpiOAuth is from http://wiki.github.com/jmathai/twitter-async/
 */
 
class OsmOAuth extends EpiOAuth
{
  const OSMOAUTH_SIGNATURE_METHOD = 'HMAC-SHA1';
  protected $requestTokenUrl = 'https://www.openstreetmap.org/oauth/request_token';
  protected $accessTokenUrl  = 'https://www.openstreetmap.org/oauth/access_token';
  protected $authorizeUrl    = 'https://www.openstreetmap.org/oauth/authorize';
  public $apiUrl          = 'http://api06.dev.openstreetmap.org/api/0.6';

  //Magic function. Intercepts all function calls.
  public function __call($name, $params = null)
  {
    //This turns the name of a php function call into an API URL. Very generic.
    $parts  = explode('_', $name);
    $method = strtoupper(array_shift($parts));
    //$parts  = implode('/', $parts);
    //$url    = $this->apiUrl . '/' . $parts;
    $parts  = implode('_', $parts);
    $url    = $this->apiUrl . '/' . preg_replace('/[A-Z]|[0-9]+/e', "'/'.strtolower('\\0')", $parts) ;
	
    if(!empty($params))
      $args = array_shift($params);

print "<Br>>>URL:" . $url ."<Br>";

    //Invoke 'httpRequest' function in the parent EpiOAuth class passing the bits n bobs extracted above
    //Result is an EpiCurl response object. Note this doesn't contain response data immediately because the request itself hasn't been sent, only queued
    return call_user_func( array($this, 'httpRequest') , $method, $url, $args);
    
  }

  public function __construct($consumerKey = null, $consumerSecret = null, $oauthToken = null, $oauthTokenSecret = null)
  {
    parent::__construct($consumerKey, $consumerSecret, self::OSMOAUTH_SIGNATURE_METHOD);
    $this->setToken($oauthToken, $oauthTokenSecret);
  }
}

