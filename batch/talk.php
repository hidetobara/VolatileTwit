<?php
ini_set( 'display_errors', 1 );
require_once( '../configure.php' );

require_once( INCLUDE_DIR . "twitter/twitter.class.php" );
require_once( INCLUDE_DIR . "learn/TalkManager.class.php" );


class TryTalk
{
	const TALK_COUNT = 3;
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
		$this->talker->init();
	}
	
	function run()
	{
		$best = array( 'text' => 'にゃーん。', 'rate' => 0.0 );
		for( $c = 0; $c < self::TALK_COUNT; $c++ )
		{
			$talk = $this->talker->talk();
			if( $talk['rate'] > $best['rate'] ) $best = $talk;
		}
		
		$options = array( 'status' => $best['text'] );
		$response = $this->oauth->post( self::URL_UPDATE_STATUS, $options );
		var_dump($response);
	}
}
$try = new TryTalk();
$try->run();
?>