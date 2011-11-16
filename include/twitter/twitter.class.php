<?php
require_once( INCLUDE_DIR . "twitter/twitteroauth.php" );

/*
 * ステータスログを読み取る
 */
class TwitterLog
{
	private $file;
	private $line;
	public $count = 0;
	
	public function open($path)
	{
		if( !is_file($path) ) return false;
		$this->file = gzopen( $path, "rb" );
		$this->line = null;
		if( !$this->file ) return false;
		print "\topend {$path}\n";
		return true;
	}
	public function read1Line()
	{
		$this->line = gzgets($this->file);
		if( !$this->line ) return null;
		$this->count++;
		return $this;
	}
	public function getArray()
	{
		if( !$this->line ) return null;
		$line = rtrim( $this->line );
		if( preg_match('/^\s*#/', $line) ) return array();
		
		$cells = mb_split( ",", $line );
		$array = array();
		foreach( $cells as $cell )
		{
			$kv = mb_split( "=", $cell, 2 );
			if(count($kv)>=2) $array[$kv[0]] = $kv[1];
		}
		return $array;
	}
	public function getArrayPassedMecab()
	{
		$array = $this->getArray();
		if($array['text']) $array['mecab'] = mecab($array['text']);
		return $array;
	}
	public function getStatusObject()
	{
		$array = $this->getArray();
		return new TwitterStatus($array);
	}
	public function close()
	{
		gzclose($this->file);
		$this->file = null;
		$this->line = null;
	}
}

/*
 * ステータスの格納
 */
class TwitterStorage
{
	const NAME_USER_FILE = 'twitter_user.csv';

	public $lastId;
	
	public $listStatus;
	public $listUser;
	
	function __construct( $opt=null )
	{
		$this->lastId = $opt['last_id'] ? $opt['last_id'] : 0;
		
		$this->listStatus = array();
		$this->listUser = array();
	}
	
	function retrieveStatusFromXml( $context )
	{
		$xml = simplexml_load_string( $context );
		if( $xml->hash->error )
		{
			throw new Exeption( $xml->hash->error );
		}
		
		foreach( $xml->status as $element )
		{
			$s = new TwitterStatus( $element );
			$this->listStatus[ $s->id ] = $s;
			if( !$this->lastId || $this->lastId < $s->id ) $this->lastId = $s->id;
		}
		ksort( $this->listStatus );
		
		foreach( $this->listStatus as $status )
		{
			$this->listUser[ $status->user->id ] = $status->user;
		}
	}

	function retrieveStatusFromLine( $line )
	{
		$line = rtrim( $line );
		$cells = mb_split( ",", $line );
		$list = array();
		foreach( $cells as $cell )
		{
			$kv = mb_split( "=", $cell );
			if( count($kv)==2 ) $list[ $kv[0] ] = $kv[1];
		}
		
		$s = new TwitterStatus( $list );
		
		$u = $this->listUser[ $s->user->id ];
		if( $u ) $s->user = $u;
		$this->listStatus[ $s->id ] = $s;
		if( !$this->lastId || $this->lastId < $s->id ) $this->lastId = $s->id;
		return $s;
	}
	
	function getNewStatusList()
	{
		$list = array();
		foreach( $this->listStatus as $sid => $status )
		{
			if( $this->lastId && $this->lastId >= $sid ) continue;
			$list[ $sid ] = $status;
		}
		return $list;
	}
	
	function saveStatus()
	{
		foreach( $this->getNewStatusList() as $sid => $status )
		{
			$path = LOG_DIR . "status/" . date("Ymd", $status->created_at) . ".log";
			$dir = dirname( $path );
			if( !is_dir($dir) ) mkdir( $dir, 0777, true );
			$line = sprintf( "%s,%s\n", $sid, $status->toCsv() );
			file_put_contents( $path, $line, FILE_APPEND );
			
			$this->lastId = $sid;
		}
	}
	
	function loadUserFromFile( $path=null )
	{
		if( !$path ) $path = LOG_DIR . self::NAME_USER_FILE;
		if( !is_file($path) ) return;
	
		$fp = fopen( $path, "r" );
		while( $line = fgets($fp) )
		{
			$line = rtrim( $line );
			$cells = mb_split( ",", $line );
			$list = array();
			foreach( $cells as $cell )
			{
				$kv = mb_split( "=", $cell );
				if( count($kv)==2 ) $list[ $kv[0] ] = $kv[1];
			}
			$u = new TwitterUser( $list );
			if( !$u->id || !$u->screen_name ) continue;
			
			$this->listUser[ $u->id ] = $u;
		}
	}
	
	function saveUser( $path=null )
	{
		if( !$path ) $path = LOG_DIR . self::NAME_USER_FILE;
		if( count($this->listUser)==0 ) return;
		$dir = dirname( $path );
		if( !is_dir($dir) ) mkdir( $dir, 0777, true );

		ksort( $this->listUser );
		
		$fp = fopen( $path, "w" );
		if( !$fp ) return;
		
		foreach( $this->listUser as $uid => $user )
		{
			fprintf( $fp, "%s,%s\n", $uid, $user->toCsv() );
		}
		fclose( $fp );
	}
}

class TwitterStatus
{
	public $id;
	public $created_at;
	public $text;
	public $reply_to;
	
	public $user;// class instance
	
	function __construct( $a )
	{
		if(is_a($a,"SimpleXMLElement")) $this->copyElement( $a );
		if(is_array($a)) $this->copyArray( $a );
	}

	function copyElement( $element )
	{
		$this->id = (string)$element->id;
		$this->created_at = strtotime( (string)$element->created_at );
		$this->text = (string)$element->text;
		$this->text = mb_ereg_replace("[,\r\n]+", " ", $this->text);
		$this->reply_to = $element->in_reply_to_status_id ? (string)$element->in_reply_to_status_id : null;
		
		$user = new TwitterUser( $element->user );
		$this->user = $user;
	}

	function copyArray( $a )
	{
		if( is_numeric($a['id']) ) $this->id = $a['id'];
		if( is_string($a['created_at']) ) $this->created_at = strtotime( $a['created_at'] );
		if( is_string($a['text']) ) $this->text = $a['text'];
		if( $a['reply_to'] ) $this->reply_to = $a['reply_to'];
		
		$this->user = new TwitterUser( $a );
	}
	
	function toCsv()
	{
		$created = date("Y-m-d H:i:s", $this->created_at);
		return "id={$this->id},"
			. "user_id={$this->user->id},"
			. "user_screen_name={$this->user->screen_name},"
			. "created_at={$created},"
			. ($this->reply_to ? "reply_to={$this->reply_to}," : "")
			. "text={$this->text}";
	}
}

class TwitterUser
{
	public $id;
	public $name;
	public $screen_name;
	public $profile_image_url;
	
	function __construct( $a )
	{
		if(is_a($a,"SimpleXMLElement")) $this->copyElement( $a );
		if(is_array($a)) $this->copyArray( $a );
	}

	function copyElement( $element )
	{
		$this->id = (string)$element->id;
		$this->name = (string)$element->name;
		$this->screen_name = (string)$element->screen_name;
		$this->profile_image_url = (string)$element->profile_image_url;
	}
	function copyArray( $a )
	{
		if( is_numeric($a['user_id']) ) $this->id = $a['user_id'];
		if( $a['user_name'] ) $this->name = $a['user_name'];
		if( $a['user_screen_name'] ) $this->screen_name = $a['user_screen_name'];
		if( $a['user_image_url'] ) $this->profile_image_url = $a['user_image_url'];
	}
	
	function toCsv()
	{
		return "user_id={$this->id},"
			. "user_name={$this->name},"
			. "user_screen_name={$this->screen_name},"
			. "user_image_url={$this->profile_image_url}";
	}
}

?>