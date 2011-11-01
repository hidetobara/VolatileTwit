<?php
require_once( INCLUDE_DIR . "learn/VolatileTwitBase.class.php" );


class VolatileTwitHajime extends VolatileTwitBase
{
	function __construct()
	{
		$this->defaultTalk = 'うんこ';
		$this->name = 'hajimehoshi';
		$this->target = 1;
		
		$this->userKey = HAJIME_OAUTH_KEY;
		$this->userSecret = HAJIME_OAUTH_SECRET;
		
		$this->initTwitter();
		$this->initLearn();
	}
	
	function isTrigered()
	{
		$hour = date("H");
		$minute = date("i");
		if( $hour%2==0 && abs($minute/10)==3 ) return true;
		return false;
	}
	
	function run()
	{
		if( !$this->isTrigered() ) return;
		
		$text = $this->bestTalk();
		$this->postTalk($text);
	}
}