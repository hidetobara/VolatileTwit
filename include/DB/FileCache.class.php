<?php

class FileCache
{
	function set( $key, array $data, DateTime $expire=null )
	{
		if( is_null($key) ) return;
		if( !$expire ) $expire = new DateTime( "+1 year" );
		
		$json = json_encode( $data );
		file_put_contents( $this->path($key), $expire->format("Y-m-d H:i:s") . "\n" . $json );
	}
	
	function get( $key )
	{
		if( is_null($key) ) return null;
		if( !is_file($this->path($key)) ) return null;
		
		$file = fopen( $this->path($key), "r" );
		$line = fgets( $file );
		$expire = new DateTime( $line );
		$now = new DateTime();
		if( $expire < $now )
		{
			fclose( $file );
			return null;
		}
		
		$line = fgets( $file );
		fclose( $file );
		$data = json_decode( $line, true );
		return $data;
	}
	
	private function path( $key )
	{
		$dir = TMP_DIR . "/file_cache/";
		if( !is_dir($dir) ) mkdir( $dir, 0777, true );
		return $dir . $key;
	}
}