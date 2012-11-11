<?php
require_once( "../configure.php" );
require_once( CONF_DIR . 'path.php' );
require_once( INCLUDE_DIR . "data/KeyValueFile.class.php" );
require_once( INCLUDE_DIR . "learn/BlockState.class.php" );


class LearnState
{
	const SCORE_LIMIT = -0.003;
	private $name;
	private $state;

	function __construct($name)
	{
		$this->name = $name;
		$this->state = new BlockState();
	}

	function run( $paths )
	{
		foreach( $paths as $path )
		{
			$store = new KeyValueFile( $path );
			while( $info = $store->read() )
			{
				if( !$this->readInfo($info) ) break;
				if( $store->count() % 1000 == 0 ) printf("\t%d counts\n", $store->count());
			}
			$store->close();
		}

		$this->save();
	}

	function save()
	{
		$this->state->saveMatrix(ConfPath::stateMatrix($this->name));
		$this->state->saveText2id(ConfPath::stateTexts($this->name));
	}
	function load()
	{
		$this->state->loadMatrix(ConfPath::stateMatrix($this->name));
		$this->state->loadText2id(ConfPath::stateTexts($this->name));
	}

	private function readInfo($info)
	{
		if( $info['score'] < self::SCORE_LIMIT ) return false;

		$context = $info['text'];
		$context = mb_ereg_replace( "[\@\#][A-Za-z0-9_]+", "", $context );
		$context = mb_ereg_replace( "[「」『』【】\]\[\"\'\(\);]", " ", $context );
		$context = mb_ereg_replace( "(http:|https:)", "", $context );
		$context = mb_ereg_replace( "//[/A-Za-z0-9\.\-\_]+", "", $context );
		$context = mb_ereg_replace( "(&gt|&lt)", "", $context );

		$this->state->learn( $context );
		return true;
	}

	function test()
	{
		print mb_convert_encoding( $this->state->getnerate(), "SJIS" );
	}
}

if(false)
{
	$learn = new LearnState('hajimehoshi');
	$paths = array(
		DATA_DIR.'score.hajimehoshi.delta.csv',
		DATA_DIR.'score.hajimeh0shi.delta.csv');
}
if(true)
{
	$learn = new LearnState('shokos');
	$paths = array(
		DATA_DIR.'score.shokos.delta.csv', );
}

if(false)
{
	$learn->run($paths);
	$learn->test();
}
if(true)
{
	$learn->load();
	$learn->test();
}
?>