<?php
require_once( '../configure.php' );
require_once( CONF_DIR . 'path.php' );
require_once( INCLUDE_DIR . 'twitter/twitter.class.php' );
require_once( INCLUDE_DIR . 'learn/ReplyState.class.php' );


class ScanReply
{
	private $name;
	private $threads;
	private $replyState;

	function __construct($name)
	{
		$this->name = $name;
		$this->threads = array();
		$this->replyState = new ReplyState($this->name);
	}

	public function run()
	{
		$this->buildReplies();
		$this->scanReplies();
		$this->writeReplies();
	}

	private function buildReplies()
	{
		$loader = new TwitterLog();
		foreach( glob( ConfPath::rawStatusList() ) as $path )
		{
			$loader->open($path);
			while( $loader->read1Line() )
			{
				$info = $loader->getArray();
				if( $info['user_screen_name'] != $this->name ) continue;
				if( $info['reply_to'] ) $this->threads[ $info['reply_to'] ]['to'] = $this->pickupText( $info['text'] );
			}
			$loader->close();
		}
		printf( "\treply count = %d\n", count($this->threads) );
	}

	private function scanReplies()
	{
		$keys = array_keys( $this->threads );

		$loader = new TwitterLog();
		foreach( glob( ConfPath::rawStatusList() ) as $path )
		{
			$loader->open($path);
			while( $loader->read1Line() )
			{
				$info = $loader->getArray();
				$id = $info['id'];
				if( in_array($id,$keys) )
				{
					$this->replyState->learn(
						$this->pickupText( $info['text'] ),
						$this->threads[$id]['to'] );
				}
			}
			$loader->close();
		}
	}

	private function writeReplies()
	{
		$this->replyState->save();
	}

	private function pickupText($text)
	{
		$text = mb_ereg_replace( "[\@\#][A-Za-z0-9_]+", "", $text );
		$text = mb_ereg_replace( "(http|https)://[/A-Za-z0-9\.\-\_\?\=]+", "", $text );
		$text = mb_ereg_replace( "(&gt;|&lt;|&nbsp;)", "", $text );
		$text = mb_ereg_replace( "[ ]+", "", $text );
		return $text;
	}
}

if(false){
	$instance = new ScanReply('hajimehoshi');
	$instance->run();
}
if(true){
	$state = new ReplyState('hajimehoshi');
	$state->load();
	$best = $state->generate('寝ます');
	var_dump($best);
}
?>