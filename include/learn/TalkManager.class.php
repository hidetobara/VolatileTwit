<?php
require_once( INCLUDE_DIR . "data/FileCache.class.php" );
require_once( INCLUDE_DIR . "keywords/KeywordAnalyze.class.php" );
require_once( INCLUDE_DIR . "keywords/mecab.function.php" );
require_once( INCLUDE_DIR . "learn/Ipca.class.php" );
require_once( INCLUDE_DIR . "learn/IpcaImage.class.php" );
require_once( INCLUDE_DIR . "learn/BlockState.class.php" );


class TalkManager
{
	const CACHE_NAME = 'talk_manager_result';
	const CACHE_TIME = '+30 minute';
	
	private $initialized = false;
	
	public $state;
	public $analyze;
	public $ipca;
	public $filter;
	
	private function init()
	{
		if( $this->initialized ) return;
		
		$this->state = new BlockState();
		$this->state->loadMatrix( VOLATILE_MATRIX );
		$this->state->loadText2id( VOLATILE_TEXT );
		
		$this->analyze = new KeywordAnalyze();
		$this->analyze->loadKeywords( KEYWORD_LIST );
		
		$this->filter = new IpcaImage();
		$this->filter->load_1Line1Element( FILTER_LIST, 0, 1 );
		
		$this->ipca = new Ipca();
		$this->ipca->load( 1 );
		
		$this->initialized = true;
	}
	
	function talk( $opt=null )
	{
		$this->init();
		
		while( true )
		{
			$text =  $this->state->getnerate();
			$length = mb_strlen($text);
			if( 0 < $length && $length < 140 ) break;
		}
		$rate = $this->evaluate( $text );
		return array(
			'text' => $text,
			'rate' => $rate,
			);
	}
	
	function evaluate( $text )
	{
		$mecab = mecab( $text );
		$mecab = $this->analyze->addKeywordIndex( $mecab, array('動詞','名詞','形容詞','形容動詞') );
		
		$img = new IpcaImage();
		$img->load_mecab( $mecab );
		$img->mul( $this->filter );

		$res = new IpcaImage();
		$this->ipca->reflectProject( $img->data, $res->data, 1 );
		return $res->data[ 1 ];
	}
	
	/*
	 * generate best talk, using cache.
	 * $opt['retry']: retry limit.
	 * $opt['nocache']: not using cache, if it exist.
	 */
	function bestTalk( $opt=null )
	{
		$retry = is_numeric($opt['retry']) ? (int)$opt['retry'] : 3;
		$cache = $opt['nocache'] ? null : new FileCache();
		
		if( $cache )
		{
			$best = $cache->get( self::CACHE_NAME );
			if( $best ) return $best;
		}
		
		$best = array( 'text' => 'にゃーん', 'rate' => 0.0 );
		for( $c = 0; $c < $retry; $c++ )
		{
			$talk = $this->talk();
			if( $talk['rate'] > $best['rate'] ) $best = $talk;
		}
		
		if( $cache )
		{
			$cache->set( self::CACHE_NAME, $best, new DateTime( self::CACHE_TIME ) );
		}
		
		return $best;
	}
}
?>