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
	
	function run()
	{
		$this->reply();
		
		if( $this->isTrigered() )
		{
			$info = $this->bestTalkInfo();
			$this->postTalk($info);
		}
	}
	
	private function isTrigered()
	{
		$hoursHit = array(0,10,12,14,16,18,20,22);
		
		$hour = date("G");
		$minute = date("i");
		if( in_array($hour,$hoursHit) && floor($minute/10)==3 ) return true;
		return false;
	}
	
	private function reply()
	{
		$specials = array('hajimehoshi','shokos','shok0s','hidetobara');
		
		$generator = new ReplyState($this->name);
		$generator->load();
		
		$storage = $this->getTimeline();
		foreach( $storage->getNewStatusList() as $status )
		{
			if( in_array($status->user->screen_name,$specials) || rand(0,100)<10 )
			{
				$best = $generator->generate($status->text);
				if( $best['to'] )
				{
					$best['text'] = "@".$status->user->screen_name." ".$best['to'];
					$best['reply_to'] = $status->id;
					$this->postTalk($best);
					break;
				}
			}
		}
	}
}