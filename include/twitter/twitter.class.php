<?php
require_once( 'Log.php' );
require_once( INCLUDE_DIR . "twitter/twitteroauth.php" );


class TwitterStorage
{
	public $listStatus;
	public $listUser;
	
	function __construct()
	{
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
		
		$sinceId = 0;
		foreach( $xml->status as $element )
		{
			$s = new TwitterStatus( $element );
			$this->listStatus[ $s->id ] = $s;
			if( $sinceId < $s->id ) $sinceId = $s->id;
		}
		ksort( $this->listStatus );
		
		foreach( $this->listStatus as $status )
		{
			$this->listUser[ $status->user->id ] = $status->user;
		}
		return $sinceId;
	}

	function retrieveStatusFromLine( $line, $fromSid=null )
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
		return $s;
	}
	
	function saveStatus()
	{
		$sinceId = 0;
		
		foreach( $this->listStatus as $sid => $status )
		{
			//var_dump($status);
			$path = LOG_DIR . "status/" . date("Ymd", $status->created_at) . ".log";
			$dir = dirname( $path );
			if( !is_dir($dir) ) mkdir( $dir, 0777, true );
			$fp = fopen( $path, "a" );
			fprintf( $fp, "%s,%s\n", $sid, $status->toCsv() );
			fclose( $fp );
			
			if( $sinceId < $status->id  ) $sinceId = $status->id;
		}
		return $sinceId;
	}
	
	function loadUserFromFile( $path=null )
	{
		if( !$path ) $path = LOG_DIR . "user.csv";
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
		if( !$path ) $path = LOG_DIR . "user.csv";
		if( count($this->listUser)==0 ) return;
		$dir = dirname( $path );
		if( !is_dir($dir) ) mkdir( $dir, 0777, true );

		ksort( $this->listUser );
		
		$fp = fopen( $path, "w" );
		if( $fp )
		{
			foreach( $this->listUser as $uid => $user )
			{
				fprintf( $fp, "%s,%s\n", $uid, $user->toCsv() );
			}
			fclose( $fp );
		}
	}
}

class TwitterStatus
{
	public $id;
	public $created_at;
	public $text;
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
		
		$user = new TwitterUser( $element->user );
		$this->user = $user;
	}

	function copyArray( $a )
	{
		if( is_numeric($a['id']) ) $this->id = $a['id'];
		if( is_string($a['created_at']) ) $this->created_at = strtotime( $a['created_at'] );
		if( is_string($a['text']) ) $this->text = $a['text'];
		
		$this->user = new TwitterUser( $a );
	}
	
	function toCsv()
	{
		$created = date("Y-m-d H:i:s", $this->created_at);
		return "id={$this->id},"
			. "user_id={$this->user->id},"
			. "user_screen_name={$this->user->screen_name},"
			. "created_at={$created},"
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