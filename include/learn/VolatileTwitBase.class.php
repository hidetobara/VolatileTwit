<?php
require_once( CONF_DIR . 'path.php' );
require_once( CONF_DIR . 'secret.php' );
require_once( INCLUDE_DIR . "data/FileCache.class.php" );
require_once( INCLUDE_DIR . "twitter/twitter.class.php" );
require_once( INCLUDE_DIR . "keywords/KeywordsTable.class.php" );
require_once( INCLUDE_DIR . "keywords/mecab.function.php" );
require_once( INCLUDE_DIR . "learn/Ipca.class.php" );
require_once( INCLUDE_DIR . "learn/IpcaImage.class.php" );
require_once( INCLUDE_DIR . "learn/BlockState.class.php" );
require_once( INCLUDE_DIR . 'learn/ReplyState.class.php' );


/**
 * 10分ごとに起動されることを想定
 */
abstract class VolatileTwitBase
{
	const URL_UPDATE_STATUS = 'http://api.twitter.com/1/statuses/update.xml';
	const URL_GET_TIMELINE = 'http://api.twitter.com/1/statuses/home_timeline.xml';
	const GET_STATUS_LIMIT = 20;
	const TALK_RETRY_LIMIT = 4;
	
	protected $consumerKey = CONSUMER_KEY;
	protected $consumerSecret = CONSUMER_SECRET;
	protected $userKey;
	protected $userSecret;

	protected $target;
	protected $name;
	protected $myName;
	protected $defaultTalk;
	
	protected $initialized = false;
	protected $state;
	protected $keywords;
	protected $ipca;
	protected $filter;
	
	abstract function run();// invoked by 10min
	
	protected $twitterApi;
	protected $cache;
	protected function initTwitter()
	{
		$this->twitterApi = new TwitterOAuth(
			$this->consumerKey,
			$this->consumerSecret,
			$this->userKey,
			$this->userSecret );
		$this->cache = new FileCache();
	}
	protected function postTalk( array $info )
	{
		$options = array( 'status' => $info['text'] );
		if( $info['reply_to'] ) $options['in_reply_to_status_id'] = $info['reply_to'];
		$response = $this->twitterApi->post( self::URL_UPDATE_STATUS, $options );
		var_dump(array($info,$response));
	}
	protected function getTimelineResponse()
	{
		$options = array( 'count' => self::GET_STATUS_LIMIT, 'include_rts' => 0 );
		$response = $this->twitterApi->get( self::URL_GET_TIMELINE, $options );
		return $response;
	}
	
	protected function cacheKeyLastStatus(){		return sprintf("%s_last_status",$this->name);		}
		
	protected function initLearn()
	{
		if( $this->initialized ) return;
		
		$this->state = new BlockState();
		$this->state->loadMatrix( ConfPath::stateMatrix($this->name) );
		$this->state->loadText2id( ConfPath::stateTexts($this->name) );
		
		$this->keywords = KeywordsTable::singleton();
		$this->keywords->loadTable( ConfPath::keywords() );
		
		$this->filter = new IpcaImage();
		$this->filter->load_1Line1Element( ConfPath::keywordsFilter(), 0, 1 );
		
		$this->ipca = Ipca::singleton();
		$this->ipca->load( 1 );
		
		$this->initialized = true;
	}
	
	protected function talk( $opt=null )
	{
		while( true )
		{
			$text =  $this->state->getnerate();
			$length = mb_strlen($text);
			if( 0 < $length && $length < 140 ) break;
		}
		return $text;
	}
	
	protected function evaluateLikelihood( $text )
	{
		$mecab = mecab( $text );
		$mecab = $this->keywords->addKeywordIntoMecabInfo( $mecab, array('動詞','名詞','形容詞','形容動詞') );
		
		$img = new IpcaImage();
		$img->load_mecab( $mecab );
		$img->mul( $this->filter );

		$res = new IpcaImage();
		$this->ipca->reflectProject( $img->data, $res->data, $this->target );
		return $res->data[ $this->target ];
	}
	
	/*
	 * generate best talk.
	 */
	protected function bestTalkInfo()
	{
		$best = array( 'text' => $this->defaultTalk, 'rate' => -0.01 );
		for( $c = 0; $c < self::TALK_RETRY_LIMIT; $c++ )
		{
			$text = $this->talk();
			$rate = $this->evaluateLikelihood($text);
			if( $rate > $best['rate'] ) $best = array('text'=>$text,'rate'=>$rate);
		}
		return $best;
	}
}