<?php
require_once( '../configure.php' );
require_once( CONF_DIR . 'path.php' );
require_once( INCLUDE_DIR . 'keywords/KeywordsTable.class.php' );
require_once( INCLUDE_DIR . 'twitter/twitter.class.php' );
require_once( INCLUDE_DIR . 'learn/Ipca.class.php' );
require_once( INCLUDE_DIR . 'learn/IpcaImage.class.php' );


class ScoreLogs
{
	private $target = 1;
	
	private $ipca;
	private $filter;
	
	private $infos;
	private $scores;
	
	function __construct()
	{
		$this->ipca = Ipca::singleton();
		$this->ipca->load();
	}
	
	function setTarget( $id ){	$this->target = $id;	}
	function setFilter( $path )
	{
		$this->filter = new IpcaImage();
		$this->filter->load_1Line1Element( $path, 0, 1 );
	}
	
	function printCount()
	{
		printf("\tcount=%d\n", count($this->scores));
	}
	
	function score( $info )
	{
		$img = new IpcaImage();
		$img->load_mecab( $info['mecab'] );
		$img->mul( $this->filter );
		
		$res = new IpcaImage();
		$this->ipca->reflectProject( $img->data, $res->data, $this->target );
		$score = $res->data[ $this->target ];

		unset($info['mecab']);
		unset($info['user_screen_name']);
		unset($info['user_id']);
		$tid = $info['id'];
		$this->infos[ $tid ] = $info;
		$this->scores[ $tid ] = $score;
	}
	function save()
	{
		arsort( $this->scores );

		$path = ConfPath::scoreUser($this->target);
		$file = fopen( $path, "w" );
		$lsstText = '';
		foreach( $this->scores as $tid => $score )
		{
			$info = $this->infos[$tid];
			if( $lastText == $info['text'] ) continue;
			$lastText = $info['text'];
			
			$list = array();
			foreach( $info as $key => $value ) $list[] = $key."=".$value;
			fprintf( $file, "score=%f,%s\n", $score, implode(",",$list) );
		}
		fclose($file);
	}
}


///// run !
$users = array();
for( $i=1; $argv[$i]; $i++ ) $users[] = $argv[$i];
//$users = array(241032387);//hajimeh0shi
//$users = array(4029081);//hajimehoshi
$users = array(19187659);//shokos

$table = KeywordsTable::singleton();
$table->loadTable( ConfPath::keywords() );
$loader = new TwitterLog();
$score = new ScoreLogs();
$score->setTarget(2);
$score->setFilter( ConfPath::keywordsFilter() );
foreach( glob( ConfPath::rawStatusList() ) as $path )
{
	$loader->open($path);
	while( $loader->read1Line() )
	{
		$info = $loader->getArray();
		if( !in_array($info['user_id'],$users) ) continue;
		
		$mecab = mecab( $info['text'] );
		$info['mecab'] = $table->addKeywordIntoMecabInfo( $mecab );
		$score->score( $info );
	}
	$score->printCount();
	$loader->close();
}
$score->save();
?>