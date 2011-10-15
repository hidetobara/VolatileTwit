<?php
require_once( '../configure.php' );
require_once( INCLUDE_DIR . 'keywords/KeywordsTable.class.php' );
require_once( INCLUDE_DIR . 'twitter/twitter.class.php' );


$table = new KeywordsTable();
$loader = new TwitterLog();
foreach( glob( LOG_DIR . "status/*.log.gz" ) as $path )
{
	$loader->open($path);
	while( $loader->read1Line() )
	{
		$info = $loader->getArrayPassedMecab();
		$table->addRecordByMecab($info['mecab']);
	}
	$loader->close();
	break;
}
$table->saveTable( DATA_DIR . 'keywords.delta.csv' );
?>