<?php
require_once( '../configure.php' );
require_once( CONF_DIR . 'path.php' );
require_once( INCLUDE_DIR . 'keywords/KeywordsTable.class.php' );
require_once( INCLUDE_DIR . 'twitter/twitter.class.php' );


$table = KeywordsTable::singleton();
$loader = new TwitterLog();
foreach( glob( ConfPath::rawStatusList() ) as $path )
{
	$loader->open($path);
	while( $loader->read1Line() )
	{
		$info = $loader->getArrayPassedMecab();
		$table->addRecordByMecab($info['mecab']);
	}
	$loader->close();
}
$table->saveTable( ConfPath::keywords() );
?>