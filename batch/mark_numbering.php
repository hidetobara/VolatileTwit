<?php
require_once( '../configure.php' );
require_once( CONF_DIR . 'path.php' );

class MarkNumbering
{
	const UID_COL = 1;

	/*
	 * $users = array(1001 => 1, 1002 => 2)
	 */
	function mark( array $users )
	{
		$counters = array();

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
				$counters[ $users[$uid] ]++;
			}

			fwrite( $out, $line . "\n" );
		}
		fclose( $in );
		fclose( $out );
		var_dump( $counters );
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
//4029081=1 19187659=2 212653601=3 1=3
//hajimehoshi:4029081
//shokos:19187659
//kawango38:212653601
?>