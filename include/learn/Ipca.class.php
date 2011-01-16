<?php
/*
 * Incremental PCA
 */
class Ipca
{
	const MAIN_MAX = 32;
	const ITEM_LENGTH = 76800;
	protected $mains = array();
	protected $reflect = array();
	protected $reflectNormal;
	
	function load()
	{
		$path = IPCA_MAIN_BIN;
		if( !is_file($path) ) return;
		
		$f = fopen( $path, "rb" );
		for( $m = 0; $m < self::MAIN_MAX; $m++ )
		{
			$line = fread( $f, self::ITEM_LENGTH*4 );
			$cells = unpack( "f*", $line );
			$this->mains[ $m ] = array_slice( $cells, 0 );
		}
	}
	
	function project( &$img, &$vec )
	{
		for( $i = 0; $i < self::ITEM_LENGTH; $i++ )
		{
			$img[ $i ] -= $this->mains[ 0 ][ $i ];
		}
		
		$vec = array(1.0);
		for( $m = 1; $m < self::MAIN_MAX; $m++ )
		{
			$amt = 0.0;
			$pI = $img;
			$pM = $this->mains[ $m ];
			for( $i = 0; $i < self::ITEM_LENGTH; $i++ )
			{
				$amt += $pI[ $i ] * $pM[ $i ];
			}
			$vec[ $m ] = $amt;
		}
	}
	
	function backProject( &$vec, &$img, $targets=null )
	{
		$nrm = array(1.0);
		for( $m = 1; $m < self::MAIN_MAX; $m++ )
		{
			$pM = $this->mains[ $m ];
			$amt = 0.0;
			for( $i = 0; $i < self::ITEM_LENGTH; $i++ )
			{
				$amt += $pM[ $i ] * $pM[ $i ];
			}
			if( $amt > 0.0 ) $nrm[ $m ] = $vec[ $m ] / $amt;
			else $nrm[ $m ] = 0.0;
		}
		
		for( $i = 0; $i < self::ITEM_LENGTH; $i++ )
		{
			if( is_array($targets) && !in_array($i,$targets) ) continue; 
			
			$amt = 0.0;
			for( $m = 0; $m < self::MAIN_MAX; $m++ )
			{
				$amt += $nrm[ $m ] * $this->mains[ $m ][ $i ];
			}
			$img[ $i ] = $amt;
		}
	}
	
	function reflectProject( &$img, &$res, $t )
	{
		$this->makeReflectNormal();
	
		$line = $this->loadReflect( $t );
		if( !is_array($line) )
		{
			$line = array();
			for( $i = 0; $i < self::ITEM_LENGTH; $i++ )
			{
				$amt = 0.0;
				for( $m = 0; $m < self::MAIN_MAX; $m++ )
				{
					$amt += $nrm[ $m ] * $this->mains[ $m ][ $t ] * $this->mains[ $m ][ $i ];
				}
				$line[ $i ] = $amt;
			}
			$this->saveReflect( $line, $t );
		}

		//$amt = $this->mains[ 0 ][ $t ];//これだけのためにmainを読み込むのはコストが高い
		$amt = 0.0;
		for( $i = 0; $i < self::ITEM_LENGTH; $i++ )
		{
			$amt += $line[ $i ] * ($img[ $i ] - $this->mains[ 0 ][ $i ]);
		}
		$res[ $t ] = $amt;
	}
	
	function makeReflectNormal($remake=false)
	{
		if( !$remake && $this->reflectNormal ) return;
		
		$this->reflectNormal = array(1.0);
		for( $m = 1; $m < self::MAIN_MAX; $m++ )
		{
			$pM = $this->mains[ $m ];
			$amt = 0.0;
			for( $i = 0; $i < self::ITEM_LENGTH; $i++ )
			{
				$amt += $pM[ $i ] * $pM[ $i ];
			}
			if( $amt > 0.0 ) $this->reflectNormal[ $m ] = 1.0 / $amt;
			else $this->reflectNormal[ $m ] = 0.0;
		}	
	}
	function saveReflect( Array $line, $target )
	{
		$path = sprintf( "%sdata/reflect_%d.csv", TMP_DIR, $target );
		$fout = fopen( $path, "w" );
		foreach( $line as $index => $value )
		{
			if( $value == 0.0 ) continue;
			fprintf( $fout, "%d,%f\n", $index, $value );
		}
		fclose( $fout );
	}
	function loadReflect( $target )
	{
		$path = sprintf( "%sdata/reflect_%d.csv", TMP_DIR, $target );
		if( !is_file($path) ) return null;
		
		$fin = fopen( $path, "r" );		
		$reflect = array();
		while( $line = fgets($fin) )
		{
			$cells = mb_split( ",",$line );
			if( count($cells)!=2 ) continue;
			$reflect[ (int)$cells[0] ] = (float)$cells[1];
		}
		fclose( $fin );
		return $reflect;
	}
}

?>