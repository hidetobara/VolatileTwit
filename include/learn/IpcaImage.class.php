<?php
require_once( INCLUDE_DIR . "learn/Ipca.class.php" );

class IpcaImage
{
	private $ItemLength;
	public $data = array();
	
	function __construct()
	{
		$this->ItemLength = Ipca::ITEM_LENGTH;
		$this->data[ 0 ] = 1.0;
	}
	
	function load_mecab( $mecab )
	{
		foreach( $mecab as $item )
		{
			$index = $item[ 'keyword' ];
			if( !$index ) continue;
			$this->data[ $index ] = 1.0;
		}
	}
	
	function load_1Line1Element( $path, $colkey, $colval )
	{
		$f = fopen( $path, "r" );
		while( $line = fgets($f) )
		{
			$line = rtrim( $line );
			$cells = mb_split( ',', $line );
			$key = $cells[ $colkey ];
			$val = $cells[ $colval ];
			if( is_null($key) || is_null($val) ) continue;
		
			$this->data[ $key ] = $val;
		}
		fclose( $f );
	}
	
	function mul( IpcaImage $img )
	{
		for( $i = 0; $i < $this->ItemLength; $i++ )
		{
			$this->data[ $i ] *= $img->data[ $i ];
		}
	}	
}

?>