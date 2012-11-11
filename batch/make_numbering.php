<?php
require_once( '../configure.php' );
require_once( CONF_DIR . 'path.php' );
require_once( INCLUDE_DIR . 'keywords/KeywordsTable.class.php' );
require_once( INCLUDE_DIR . 'twitter/twitter.class.php' );


class Numbering
{
	private $file;
	public function open($path)
	{
		$this->file = fopen( $path, "w" );
		//fprintf( $this->file, "#sid,uid,reply_to,keywords...\n");
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

$table = KeywordsTable::singleton();
$table->loadTable( ConfPath::keywords() );
$loader = new TwitterLog();
$numbering = new Numbering();
$numbering->open( ConfPath::statusList() );
foreach( glob( ConfPath::rawStatusList() ) as $path )
{
	$loader->open($path);
	while( $loader->read1Line() )
	{
		$info = $loader->getArrayPassedMecab();
		$mecab = $table->addKeywordIntoMecabInfo($info['mecab']);
		$numbering->write1Line($info, $mecab);
	}
	$loader->close();
}
$numbering->close();
?>