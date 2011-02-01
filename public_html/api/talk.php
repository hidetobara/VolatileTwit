<?php
require_once( "../../configure.php" );

require_once( INCLUDE_DIR . "web/BaseApi.class.php" );
require_once( INCLUDE_DIR . "learn/TalkManager.class.php" );


class TalkApi extends BaseApi
{
	const TALK_COUNT = 3;

	function handle()
	{
		//$this->format = 'txt';
		
		$manager = new TalkManager();
		$manager->init();
		
		$best = array( 'text' => 'にゃーん。', 'rate' => 0.0 );
		for( $c = 0; $c < self::TALK_COUNT; $c++ )
		{
			$talk = $manager->talk();
			if( $talk['rate'] > $best['rate'] ) $best = $talk;
		}
		
		$this->assign( 'text', $best['text'] );
		$this->assign( 'status', 'ok' );
	}
}

$api = new TalkApi();
$api->run();
?>