<?php
require_once( "../../configure.php" );

require_once( INCLUDE_DIR . "web/BaseApi.class.php" );
require_once( INCLUDE_DIR . "keywords/BlockState.class.php" );

require_once( INCLUDE_DIR . "keywords/KeywordAnalyze.class.php" );
require_once( INCLUDE_DIR . "keywords/mecab.function.php" );
require_once( INCLUDE_DIR . "learn/Ipca.class.php" );
require_once( INCLUDE_DIR . "learn/IpcaImage.class.php" );


class TalkApi extends BaseApi
{
	const TALK_COUNT = 3;

	function handle()
	{
		$this->format = 'txt';
		
		$core = new TalkApiCore();
		$core->init();
		
		$bestText = 'にゃーん。';
		$bestRate = 0.0;
		for( $c = 0; $c < self::TALK_COUNT; $c++ )
		{
			$text = $core->talk();
			$rate = $core->evaluate( $text );
			if( $rate > $bestRate )
			{
				$bestText = $text;
				$bestRate = $rate;
			}
		}
		
		$this->assign( 'text', $bestText );
		$this->assign( 'status', 'ok' );
	}
}

class TalkApiCore
{
	public $state;
	public $analyze;
	public $ipca;
	public $filter;
	
	function init()
	{
		$this->state = new BlockState();
		$this->state->loadMatrix( VOLATILE_MATRIX );
		$this->state->loadText2id( VOLATILE_TEXT );
		
		$this->analyze = new KeywordAnalyze();
		$this->analyze->loadKeywords( KEYWORD_LIST );
		
		$this->filter = new IpcaImage();
		$this->filter->load_1Line1Element( FILTER_LIST, 0, 1 );
		
		$this->ipca = new Ipca();
		$this->ipca->load( 1 );
	}
	
	function talk()
	{
		return $this->state->getnerate();
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
}

$api = new TalkApi();
$api->run();
?>