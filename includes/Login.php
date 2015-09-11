<?php
	// if (empty($argv[1])) {
	//  print("Specify the part of the URL after 'https://my.private.wiki/wiki/index.php/' as argument.\n");
	//  exit;
	// }
include "Snoopy/Snoopy.class.php";
class Login{
	private $snoopy;
	function __construct(){
		$this->snoopy = new Snoopy;
		$this->snoopy->curl_path="/usr/bin/curl";
		$wikiroot = "http://smw.learning-socle.org";
		$api_url = $wikiroot . "/api.php";

		# Login via api.php
		$login_vars['action'] = "login";
		$login_vars['lgname'] = "Admin";
		$login_vars['lgpassword'] = "pls15";
		$login_vars['format'] = "php";
		## First part
		$this->snoopy->submit($api_url,$login_vars);
		$response = unserialize($this->snoopy->results);
		$login_vars['lgtoken'] = $response["login"]["token"];
		$this->snoopy->cookies["wiki_session"] = $response["login"]["sessionid"]; // You may have to change 'wiki_session' to something else on your Wiki
		## Second part, now that we have the token
		$this->snoopy->submit($api_url,$login_vars);
	}

	public function callApi($url){
		$this->snoopy->submit($url);
		//echo $this->snoopy->results;
		return $this->snoopy->results;
	}
	
}
	
