<?php
require_once( INCLUDE_DIR . 'keywords/KeywordsTable.class.php' );
require_once( INCLUDE_DIR . 'twitter/twitter.class.php' );
require_once( INCLUDE_DIR . 'learn/Ipca.class.php' );
require_once( INCLUDE_DIR . 'learn/IpcaImage.class.php' );


class ReplyState
{
	const DIMANSION_MAX = 17;
	private $name;

	private $filter;
	private $keywords;
	private $ipca;
	private $list;	//{from,to,dimension}
	
	function __construct( $name )
	{
		$this->name = $name;
		
		$this->filter = new IpcaImage();
		$this->filter->load_1Line1Element( ConfPath::keywordsFilter(), 0, 1 );
		
		$this->keywords = KeywordsTable::singleton();
		$this->keywords->loadTable( ConfPath::keywords() );
		
		$this->ipca = Ipca::singleton();
		$this->ipca->load( self::DIMANSION_MAX );
	}
	
	function learn( $from, $to )
	{
		$this->list[] = array(
			'from' => $from,
			'to' => $to,
			'dimension' => $this->evaluateDimension($from) );
	}
	function generate( $from, $acceptable=0.00005 )
	{
		$best = array( 'to' => null, 'distance' => $acceptable );
		$dimension = $this->evaluateDimension( $from );
		foreach( $this->list as $sample )
		{
			$distance = $this->dimensionDistance( $dimension, $sample['dimension'] );
			if( $distance > $best['distance'] ) continue;
			$best = $sample;
			$best['distance'] = $distance;
		}
		return $best;
	}
	
	protected function evaluateDimension( $text )
	{
		$mecab = mecab( $text );
		$mecab = $this->keywords->addKeywordIntoMecabInfo( $mecab, array('動詞','名詞','形容詞','形容動詞') );
		
		$img = new IpcaImage();
		$img->load_mecab( $mecab );
		$img->mul( $this->filter );

		$this->ipca->project( $img->data, $vec );
		return $vec;
	}
	
	protected function dimensionDistance( $d1, $d2 )
	{
		$sum = 0.0;
		for( $i=0; $i<self::DIMANSION_MAX; $i++ )
		{
			$tmp = $d2[$i] - $d1[$i];
			$sum += $tmp * $tmp;
		}
		return sqrt( $sum );
	}
	
	function save()
	{
		$file = fopen( ConfPath::replyList($this->name), "w" );
		foreach( $this->list as $sample )
		{
			fprintf( $file, "%s,%s,%s\n", $sample['from'], $sample['to'], implode(',',$sample['dimension']) );
		}
		fclose($file);
	}
	function load()
	{
		$file = fopen( ConfPath::replyList($this->name), "r" );
		while( $line = fgets($file) )
		{
			$line = rtrim($line);
			$cells = mb_split(',', $line);
			$sample = array(
				'from' => array_shift($cells),
				'to' => array_shift($cells),
				'dimension' => $cells );
			$this->list[] = $sample;
		}
		fclose($file);
	}	
}