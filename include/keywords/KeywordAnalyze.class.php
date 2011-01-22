<?php

class KeywordAnalyze
{
	const KEYWORD_OFFSET = 100;
	public $m_Table;
	
	function __construct( $opt=null )
	{
		$this->m_Table = array();
		if( $opt['PathKeywords'] ) $this->LoadKeywords( $opt['PathKeywords'] );
	}
	
	function loadLog( $path )
	{
		$f = fopen( $path, "r" );
		if( empty($f) ) return;
		printf( "Keywords.LoadLog(%s)\n", $path );
		
		while( $line = fgets( $f ) )
		{
			$line = rtrim( $line );
			if( substr( $line, 0, 1 )=="#" ) continue;
			
			$cells = mb_split( ":", $line );
			$text = array_pop($cells);
			if( !$text ) continue;
			
			$arr = $this->Mecab( $text );
			foreach( $arr as $a )
			{
				$word = $a['word'];
				if( empty($this->m_Table[ $word ]) )
				{
					$this->m_Table[ $word ] = 
						array( 'parse'=>$a['parse'], 'count'=>1 );
					continue;
				}
				
				$this->m_Table[ $word ]['count']++;
			}
		}
		fclose( $f );
		printf( "\t count keywords = %d\n", count($this->m_Table) );
	}
	
	function get( $word ){		return $this->m_Table[ $word ];		}
	
	function saveKeywords( $path )
	{
		ksort( $this->m_Table );
		
		$f = fopen( $path, 'w' );
		$index = self::KEYWORD_OFFSET;
		foreach( $this->m_Table as $word => $record )
		{
			$line = "{$index},word={$word}";
			foreach( $record as $item => $value ) $line .= ",{$item}={$value}";
			$line .= "\n";
			$index++;
			fputs( $f, $line );
		}
		fclose( $f );
	}
	
	/*
	 * word をキーにテーブルを構築
	 */
	function loadKeywords( $path )
	{
		$f = fopen( $path, 'r' );
		while( $line = fgets($f) )
		{
			$line = rtrim( $line );
			$cells = mb_split( ',', $line );
			
			$index = array_shift($cells);
			$items = array( 'index'=>$index );
			$word = null;
			foreach( $cells as $cell )
			{
				$kv = mb_split( '=', $cell );
				if( $kv[0] === '' || $kv[1] === '' ) continue;
				
				if( $kv[0] === 'word' ) $word = $kv[1]; else $items[ $kv[0] ] = $kv[1];
			}
			if( is_null($word) ) continue;
			
			$this->m_Table[ $word ] = $items;
		}
		fclose( $f );
	}
	
	function addKeywordIndex( $mecab, $targets=null )
	{
		foreach( $mecab as $i => $item )
		{
			$parse = $item['parse'];
			$word = $item['word'];
			if( is_array($targets) && !in_array($parse,$targets) ) continue;
			
			$data = $this->m_Table[ $word ];	
			$index = (int)$data[ 'index' ];
			if( !$index ) continue;
			
			$mecab[ $i ]['index'] = $index;
		}
		return $mecab;
	}
}
?>