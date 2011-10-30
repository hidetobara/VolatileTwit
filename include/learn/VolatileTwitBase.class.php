<?php
require_once( CONF_DIR . 'path.php' );
require_once( INCLUDE_DIR . "twitter/twitter.class.php" );
require_once( INCLUDE_DIR . "keywords/KeywordsTable.class.php" );
require_once( INCLUDE_DIR . "keywords/mecab.function.php" );
require_once( INCLUDE_DIR . "learn/Ipca.class.php" );
require_once( INCLUDE_DIR . "learn/IpcaImage.class.php" );
require_once( INCLUDE_DIR . "learn/BlockState.class.php" );

/**
 * 10分ごとに起動されることを想定
 */
abstract class VolatileTwitBase
{
	const URL_UPDATE_STATUS = "http://api.twitter.com/statuses/update.xml";
	
	protected $consumerKey = 'up3hbQ1q72R9hY8lZHkDiA';
	protected $consumerSecret = 'WTWBLeytOJeE1PaojFFe59dLBRd8jW8dEk6hkUoQxc';
	protected $userKey;
	protected $userSecret;

	protected $target;
	protected $name;
	protected $retry = 3;
	protected $defaultTalk;
	
	abstract function run();
	
	protected $twitterApi;
	protected function initTwitter()
	{
		$this->twitterApi = new TwitterOAuth(
			$this->consumerKey,
			$this->consumerSecret,
			$this->userKey,
			$this->userSecret );
	}
	protected function postTalk($text)
	{
		$options = array( 'status' => $text );
//		$response = $this->twitterApi->post( self::URL_UPDATE_STATUS, $options );
		var_dump(array($options,$response));
	}
	
	protected $initialized = false;
	protected $state;
	protected $keywords;
	protected $ipca;
	protected $filter;
	
	protected function initLearn()
	{
		if( $this->initialized ) return;
		
		$this->state = new BlockState();
		$this->state->loadMatrix( ConfPath::stateMatrix($this->name) );
		$this->state->loadText2id( ConfPath::stateTexts($this->name) );
		
		$this->keywords = new KeywordsTable();
		$this->keywords->loadTable( ConfPath::keywords() );
		
		$this->filter = new IpcaImage();
		$this->filter->load_1Line1Element( ConfPath::keywordsFilter(), 0, 1 );
		
		$this->ipca = new Ipca();
		$this->ipca->load( $this->target );
		
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
	
	protected function evaluate( $text )
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
	protected function bestTalk()
	{
		$best = array( 'text' => $this->defaultTalk, 'rate' => 0.0 );
		for( $c = 0; $c < $this->retry; $c++ )
		{
			$text = $this->talk();
			$rate = $this->evaluate($text);
			if( $rate > $best['rate'] ) $best = array('text'=>$text,'rate'=>$rate);
		}
		return $best;
	}
}