<?php
/*
 * Incremental PCA
 */
class Ipca
{
	const MAIN_MAX = 32;
	const ITEM_LENGTH = 76800;
	protected $mains = array();
	
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
		
		$vec = array(0.0);
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
}

?>