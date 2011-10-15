<?php
require_once( '../configure.php' );
require_once( INCLUDE_DIR . 'keywords/KeywordsTable.class.php' );
require_once( INCLUDE_DIR . 'twitter/twitter.class.php' );


class Numbering
{
	private $file;
	public function open($path)
	{
		$this->file = fopen( $path, "w" );
		fprintf( $this->file, "#sid,uid,reply_to,keywords...\n");
	}
	public function write1Line( $info, $mecab )
	{
		if( !is_array($mecab) || count($mecab)==0 ) return;
		$numbers = array();
		foreach( $mecab as $item )
		{
			if($item['keyword']) $numbers[] = $item['keyword'];
		}
		fprintf( $this->file, "%s,%d,%d,%s\n", $info['id'], $info['user_id'], $info['reply_to'],
				implode(",",$numbers) );
	}
	public function close()
	{
		fclose($this->file);
	}
}

$table = new KeywordsTable();
$table->loadTable( DATA_DIR . 'keywords.delta.csv' );
$loader = new TwitterLog();
$numbering = new Numbering();
$numbering->open( DATA_DIR . 'status_list.delta.csv' );
foreach( glob( LOG_DIR . "status/*.log.gz" ) as $path )
{
	$loader->open($path);
	while( $loader->read1Line() )
	{
		$info = $loader->getArrayPassedMecab();
		$mecab = $table->addKeywordIntoMecabInfo($info['mecab']);
		$numbering->write1Line($info, $mecab);
	}
	$loader->close();
	break;
}
$numbering->close();
?>