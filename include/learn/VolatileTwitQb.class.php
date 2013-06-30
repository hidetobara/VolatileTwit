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

		$this->initTwitter(QB_OAUTH_KEY, QB_OAUTH_SECRET);
		$this->initLearn();
	}

	function isTrigered()
	{
		$hoursHit = array(4,15);

		$hour = date("G");
		$minute = date("i");
		if( in_array($hour,$hoursHit) && floor($minute/10)==1 ) return true;
		return false;
	}

	function run()
	{
		if( !$this->isTrigered() ) return;

		$info = $this->bestTalkInfo();
		$this->postTalk($info);
	}
}
