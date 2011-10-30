<?php
require_once( INCLUDE_DIR . 'keywords/mecab.function.php' );

class KeywordsTable
{
	const KEYWORD_OFFSET = 100;
	public $m_Table;
	
	function __construct( $opt=null )
	{
		$this->m_Table = array();
		if( $opt['PathKeywords'] ) $this->loadTable( $opt['PathKeywords'] );
	}
	
	function addRecordByMecab( $mecab )
	{
		if( !is_array($mecab) ) return;

		foreach( $mecab as $a )
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
	
	function get( $word ){		return $this->m_Table[ $word ];		}
	
	function saveTable( $path )
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
	function loadTable( $path )
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
		if(ENV_TYPE != 'RELEASE') printf( "Keywords table(%s) loaded\n", $path );
	}
	
	function addKeywordIntoMecabInfo( $mecab, $targets=null )
	{
		if( !is_array($mecab) ) return array();

		foreach( $mecab as $i => $item )
		{
			$parse = $item['parse'];
			$word = $item['word'];
			if( is_array($targets) && !in_array($parse,$targets) ) continue;
			
			$data = $this->m_Table[ $word ];	
			$index = (int)$data[ 'index' ];
			if( !$index ) continue;
			
			$mecab[ $i ]['keyword'] = $index;
		}
		return $mecab;
	}
}
?>