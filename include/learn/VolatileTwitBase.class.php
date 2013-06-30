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
	const GET_STATUS_LIMIT = 20;

	protected $talkRetryLimit = 3;

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

	protected $api;
	protected $cache;
	protected function initTwitter( $key, $secret )
	{
		$this->twitter = new TwitterApi( $key, $secret );
		$this->cache = new FileCache();
	}
	protected function postTalk( array $info )
	{
		$opt = array();
		if( $info['reply_to'] ) $opt['in_reply_to_status_id'] = $info['reply_to'];
		$response = $this->twitter->updateStatus( $info['text'], $opt );
		var_dump(array($info,$response));
	}

	protected function cacheKeyLastStatus(){		return sprintf("user_%s_status",$this->name);		}

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
		$this->ipca->load( 3 );

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

	public function evaluateLikelihood( $text )
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
	public function bestTalkInfo()
	{
		$best = array( 'text' => $this->defaultTalk, 'rate' => -0.01 );
		for( $c = 0; $c < $this->talkRetryLimit; $c++ )
		{
			$text = $this->talk();
			$rate = $this->evaluateLikelihood($text);
			if( $rate > $best['rate'] ) $best = array('text'=>$text,'rate'=>$rate);
		}
		return $best;
	}
}