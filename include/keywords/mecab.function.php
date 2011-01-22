<?php

function mecab( $text )
{
	$inPath = TMP_DIR . "in.txt";
	$outPath = TMP_DIR . "out.txt";
	file_put_contents( $inPath, $text );
	$command = sprintf( "%s %s > %s", MECAB_EXE, $inPath, $outPath );
	system( $command );
	
	$out = array();
	$f = fopen( $outPath, "r" );
	while( $line = fgets( $f ) )
	{
		$line = rtrim( $line );
		$blocks = mb_split( "\t", $line );
		if( empty($blocks[1]) ) continue;
	
		$cells = mb_split( ",", $blocks[1] );
		if( preg_match('@[\*\s]@',$cells[6]) && $cells[0] != "名詞" ) continue;
		
		$out[] = array( 'origin'=>$blocks[0],'word'=>$cells[6], 'parse'=>$cells[0] );
	}
	return $out;
}
?>