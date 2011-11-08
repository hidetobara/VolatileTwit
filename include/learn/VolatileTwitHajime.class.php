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
		$hoursHit = array(0,10,12,14,16,18,20,22);
		
		$hour = date("G");
		$minute = date("i");
		if( in_array($hour,$hoursHit) && floor($minute/10)==3 ) return true;
		return false;
	}
	
	function run()
	{
		if( !$this->isTrigered() ) return;
		
		$info = $this->bestTalkInfo();
		$this->postTalk($info);
	}
}