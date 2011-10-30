<?php
require_once( '../configure.php' );


class MarkNumbering
{
	const UID_COL = 1;
		
	/*
	 * $users = array(1001 => 1, 1002 => 2)
	 */
	function mark( array $users )
	{
		$in = fopen( ConfPath::statusList(), 'r' );
		$out = fopen( ConfPath::statusListMarked(), 'w' );
		while( $line = fgets($in) )
		{
			$line = rtrim( $line );
			$cells = mb_split( ',', $line );
			$uid = $cells[ self::UID_COL ];
			
			if( $users[$uid] )
			{
				$line .= "," . $users[$uid];
			}
			
			fwrite( $out, $line . "\n" );
		}
		fclose( $in );
		fclose( $out );
	}
}

$users = array();
for( $i=1; $argv[$i]; $i++ )
{
	$kv = mb_split("=", $argv[$i]);
	if(count($kv)==2) $users[$kv[0]] = $kv[1];
}
$marker = new MarkNumbering();
$marker->mark( $users );
?>