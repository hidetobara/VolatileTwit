<?php
require_once( "../configure.php" );
require_once( INCLUDE_DIR . "keywords/BlockState.class.php" );

$state = new BlockState();
/*
$state->learn("プログラミングの本書きます。");
$state->learn("男子が特に何かした覚えはないけど");
$state->learn("男子が集まる");
$state->learn("女子も集まる");
$state->learn("ホワイトノイズは音量や人種に関係なく同じ");
$state->learn("女子も一番暇な人種であった");
$state->saveMatrix("matrix.csv");
$state->saveText2id("text.csv");
*/
/*
$path = "C:/obara/Chamomile/data/sort_cmt.gamma.csv";
$life = 5000;
$similarity = 0.02;

$fin = fopen( $path, "r" );
while( $line = fgets($fin) )
{
	$cells = mb_split( ",", rtrim($line) );
	$context = $cells[3];
	if( $cells[2] < $similarity ) break;
	if( !$context ) continue;

	$context = mb_ereg_replace( "[\@\#][A-Za-z0-9_]+", "", $context );
	$context = mb_ereg_replace( "[「」（）【】\(\)\"\']", "", $context );

	$state->learn( $context );

	$life--;
	if( $life < 0 ) break;
	if( $life % 1000 == 0 ) print "life is {$life}\n";
}
fclose( $fin );

$state->saveMatrix("matrix.csv");
$state->saveText2id("text.csv");
*/
$state->loadMatrix("matrix.csv");
$state->loadText2id("text.csv");

print $state->getnerate();
?>