<?php
require_once( INCLUDE_DIR . "learn/VolatileTwitBase.class.php" );


class VolatileTwitHajime extends VolatileTwitBase
{
	function __construct()
	{
		$this->talkRetryLimit = 4;
		$this->defaultTalk = 'うんこ';
		$this->name = 'hajimehoshi';
		$this->myName = 'hajimeh0shi';
		$this->target = 1;

		$this->initTwitter(HAJIME_OAUTH_KEY, HAJIME_OAUTH_SECRET);
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
		$generator = new ReplyState($this->name);
		$generator->load();

		$box = $this->cache->get( $this->cacheKeyLastStatus() );
		$timelines = $this->twitter->getHomeTimeline( $box );
		$storage = new TwitterStorage();
		$storage->retrieveStatus( $timelines );

		foreach( $storage->listStatus as $status )
		{
			if( $this->isMyTweet($status) ) continue;

			if( ($this->isSpecialTweet($status) && $this->invoker(5))
				|| $this->isReplyTweetForMe($status) )
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

		$this->cache->set( $this->cacheKeyLastStatus(), $storage->updateUserCache( $box ) );
	}
	private function isMyTweet($status)
	{
		return $status->user->screen_name == $this->myName;
	}
	private function isSpecialTweet($status)
	{
		$specials = array('hajimehoshi','shokos','shok0s','hidetobara','yoshiori','shimacpyon','kwappa',
			'ugdark','takano32','tkzwtks','yamashiro','slightair','Omegamega','fat47');
		return in_array($status->user->screen_name,$specials);
	}
	private function isReplyTweetForMe($status)
	{
		return strpos($status->text, "@".$this->myName) !== false;
	}
	private function invoker($percent)
	{
		return rand(0,99) < $percent;
	}
}