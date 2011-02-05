<?php
ini_set( 'display_errors', 1 );
require_once( '../configure.php' );

require_once( INCLUDE_DIR . "twitter/twitter.class.php" );
require_once( INCLUDE_DIR . "learn/TalkManager.class.php" );


class BatchTalk
{
	const URL_UPDATE_STATUS = "http://api.twitter.com/statuses/update.xml";
	
	public $oauth;
	public $talker;
	
	function __construct()
	{
		$this->oauth = new TwitterOAuth(
			CONSUMER_KEY,
			CONSUMER_SECRET,
			OAUTH_KEY,
			OAUTH_SECRET
			);
		$this->talker = new TalkManager();
	}
	
	function run()
	{
		$best = $this->talker->bestTalk();
		
		$options = array( 'status' => $best['text'] );
		$response = $this->oauth->post( self::URL_UPDATE_STATUS, $options );
		var_dump(array($options,$response));
	}
}
$try = new BatchTalk();
$try->run();
?>