<?php
require_once( INCLUDE_DIR . "keywords/mecab.function.php" );

/*
 * 文章の遷移状態を登録する
 */
class Block
{
	const HEAD = 1;
	const TAIL = 2;
	const WORDS = 3;
		
	public $id;
	public $words = array();
	public $text;
	function __construct($type=3){	$this->type = $type;	}
}

class BlockState
{
	const TEXT_LIMIT = 256;
	private $splitParses = array('動詞','名詞','形容詞','形容動詞','副詞');
	
	protected $matrix;
	protected $text2id;
	protected $id2text;
	
	function __construct()
	{
		$this->matrix = array(
			Block::HEAD => array( Block::TAIL => 1 ) );
		$this->text2id = array();
		$this->id2text = array();
	}
	
	function learn( $text )
	{
		$mecab = mecab( $text );
		$blocks = $this->mecab2blocks( $mecab );
		
		$pre = null;
		foreach( $blocks as $block )
		{	
			if( is_null($block) ) continue;
			if( is_null($pre) )
			{
				$pre = $this->getBlockId( $block );
				continue;
			}

			$now = $this->getBlockId( $block );	//var_dump($block);
			$this->matrix[ $pre ][ $now ]++;

			$pre = $now;
		}
	}
	
	function getnerate()
	{
		$now = Block::HEAD;
		$context = '';
		
		while(true)
		{
			$nexts = $this->matrix[ $now ];
			$amount = 0;
			foreach($nexts as $count) $amount += $count;
			$life = rand(0, $amount);	//var_dump(array($now,$amount,$life));
			foreach($nexts as $next => $count)
			{
				$life -= $count;
				if( $life <= 0 )
				{
					$now = $next;
					$context .= $this->modifyWord( $this->id2text[ $now ] );
					break;
				}
			}
			if( mb_strlen($context) > self::TEXT_LIMIT ) break;
			if( $now == Block::TAIL ) break;
		}
		return $context;
	}

	private function modifyWord( $word )
	{
		if( mb_ereg('[_a-zA-Z0-9]+$', $word) ) $word .= " ";
		return $word;
	}
	
	function saveMatrix( $path )
	{
		$fout = fopen( $path, "w" );
		foreach( $this->matrix as $now => $nexts )
		{
			$line = $now;
			foreach( $nexts as $next => $count ) $line .= ",{$next}={$count}";
			fwrite( $fout, $line . "\n" );
		}
		fclose( $fout );
	}
	function loadMatrix( $path )
	{
		$fin = fopen( $path, "r" );
		while( $line = fgets($fin) )
		{
			$cells = mb_split( ",", rtrim($line) );
			$now = array_shift( $cells );
			$matrix = array();
			foreach( $cells as $cell )
			{
				$kv = mb_split( "=" ,$cell );
				if( count($kv)!=2 ) continue;
				$matrix[ $kv[0] ] = $kv[1];
			}
			$this->matrix[ $now ] = $matrix;
		}
		fclose( $fin );
	}
	
	function saveText2id( $path )
	{
		$fout = fopen( $path, "w" );
		foreach( $this->text2id as $text => $id )
		{
			fwrite( $fout, "{$text},{$id}\n" );
		}
		fclose( $fout );
	}
	function loadText2id( $path )
	{
		$fin = fopen( $path, "r" );
		while( $line = fgets($fin) )
		{
			$cells = mb_split( ",", rtrim($line) );
			if( count($cells)!=2 ) continue;
			$this->text2id[ $cells[0] ] = $cells[1];
			$this->id2text[ $cells[1] ] = $cells[0];
		}
		fclose( $fin );
	}
	
	protected function getBlockId( Block $b )
	{
		if( $b->type == Block::HEAD ) return Block::HEAD;
		if( $b->type == Block::TAIL ) return Block::TAIL;
		
		$id = $this->text2id[ $b->text ];
		if( $id ) return $id;
		
		$b->id = count( $this->id2text ) + 100;	/////Attension !!!
		$this->text2id[ $b->text ] = $b->id;
		$this->id2text[ $b->id ] = $b->text;
		return $b->id;
	}
	
	protected function mecab2blocks( $mecab )
	{
		$blocks = array();
		$block = new Block(Block::HEAD);
		
		foreach( $mecab as $word )
		{
			if( $word['origin'] == "。" )
			{
				$blocks[] = $block;
				$blocks[] = new Block(Block::TAIL);
				$blocks[] = new Block(Block::HEAD);
				$block = new Block(Block::WORDS);
				continue;
			}
			
			if( in_array($word['parse'], $this->splitParses) )
			{
				$blocks[] = $block;
				$block = new Block(Block::WORDS);
//				$block->words[] = $word;
				$block->text = $word['origin'];
 			}
 			else
 			{
//				$block->words[] = $word;
 				$block->text .= $word['origin'];
 			}
		}

		$blocks[] = $block;
		$blocks[] = new Block(Block::TAIL);
		return $blocks;
	}
}
?>