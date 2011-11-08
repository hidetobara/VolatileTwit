<?php
require_once( INCLUDE_DIR . "learn/VolatileTwitBase.class.php" );


class VolatileTwitShokos extends VolatileTwitBase
{
	function __construct()
	{
		$this->defaultTalk = 'うるせー';
		$this->name = 'shokos';
		$this->target = 2;
		
		$this->userKey = SHOKOS_OAUTH_KEY;
		$this->userSecret = SHOKOS_OAUTH_SECRET;
		
		$this->initTwitter();
		$this->initLearn();
	}
	
	function isTrigered()
	{
		$hoursHit = array(11,13,15,17,19,21);

		$hour = date("G");
		$minute = date("i");
		if( in_array($hour,$hoursHit) && floor($minute/10)==0 ) return true;
		return false;
	}
	
	function run()
	{
		if( !$this->isTrigered() ) return;
		
		$info = $this->bestTalkInfo();
		$this->postTalk($info);
	}
}