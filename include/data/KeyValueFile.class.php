<?php
class KeyValueFile
{
	private $handle;
	private $count;
	
	function __construct( $path )
	{
		$this->handle = fopen( $path,"r" );
	}
	function read()
	{
		if( !$this->handle ) return null;
		$line = fgets($this->handle);
		if( !$line ) return null;
		
		$this->count++;

		$line = rtrim($line);
		$cells = mb_split(',',$line);
		$list = array();
		foreach( $cells as $cell )
		{
			$kv = mb_split('=',$cell,2);
			$list[ $kv[0] ] = $kv[1];
		}
		return $list;
	}
	
	function close(){		fclose($this->handle);		}
	function count(){		return $this->count;		}
}