<?php
require_once( INCLUDE_DIR . "learn/VolatileTwitBase.class.php" );


class VolatileTwitQb extends VolatileTwitBase
{
	function __construct()
	{
		$this->defaultTalk = '…';
		$this->name = 'qb';
		$this->myName = 'kawanqb38';
		$this->target = 3;

		$this->userKey = QB_OAUTH_KEY;
		$this->userSecret = QB_OAUTH_SECRET;

		$this->initTwitter();
		$this->initLearn();
	}

	function isTrigered()
	{
		$hoursHit = array(7);

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
