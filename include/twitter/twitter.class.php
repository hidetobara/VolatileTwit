<?php
require_once( INCLUDE_DIR . "twitter/twitteroauth.php" );


/*
 * API アクセス
 */
class TwitterApi
{
	const GET_STATUS_MAX = 200;
	const URL_HOME_TIMELINE = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
	const URL_UPDATE_STATUS = 'https://api.twitter.com/1.1/statuses/update.json';

	protected $oauth;

	function __construct( $key, $secret )
	{
		$this->oauth = new TwitterOAuth(
				CONSUMER_KEY,
				CONSUMER_SECRET,
				$key,
				$secret
		);
	}

	function getHomeTimeline( $sinceId=null )
	{
		$o = array( 'count' => self::GET_STATUS_MAX );
		if( $sinceId ) $o['since_id'] = $sinceId;
		$r = $this->oauth->get( self::URL_HOME_TIMELINE, $o );
		return json_decode($r, true);
	}

	function updateStatus( $text )
	{
		$o = array( 'status'=>$text );
		$r = $this->oauth->post( self::URL_UPDATE_STATUS, $o );
		return json_decode($r, true);
	}
}


/*
 * ステータスの格納、保存、読み込み
 */
class TwitterStorage
{
	public $lastId;

	public $listStatus;
	public $listUser;

	function __construct( $opt=null )
	{
		$this->lastId = 0;

		$this->listStatus = array();
		$this->listUser = array();
	}

	function loadStatus( $path )
	{
		$gz = gzopen($path, "rb");
		while( $line = gzgets($gz) )
		{
			$a = json_decode($line, true);
			$s = new TwitterStatus($a);
			$this->listStatus[] = $s;
			if($s->user) $this->listUser[$s->user->screen_name] = $s->user;

			if($this->lastId < $s->id){
				$this->lastId = $s->id;
			}
		}
		gzclose($gz);
	}

	function saveStatus( $path )
	{
		$this->prepareDir($path);

		$f = fopen($path, "w");
		foreach( $this->listStatus as $s )
		{
			fwrite($f, json_encode($s->toArray(), JSON_UNESCAPED_UNICODE) . "\n");
		}
		fclose($f);
	}

	function saveStatusByDate( $dir )
	{

		$f = null;
		$prepath = null;
		foreach( $this->listStatus as $s )
		{
			$path = sprintf( "%s%s.json", $dir, date("Ymd", $s->created_at) );
			$this->prepareDir( $path );
			if( $path != $prepath ){
				if( $f ) fclose( $f );
				$f = fopen( $path, "a" );
			}
			fwrite($f, json_encode($s->toArray(), JSON_UNESCAPED_UNICODE) . "\n");
			$prepath = $path;
		}
		if( $f ) fclose( $f );
	}

	private function prepareDir( $path )
	{
		$dir = dirname( $path );
		if( !is_dir($dir) ) mkdir( $dir, 0777, true );
	}

	function retrieveStatus( $array )
	{
		foreach( $array as $a )
		{
			$s = new TwitterStatus($a);
			$this->listStatus[] = $s;
			$this->listUser[$s->user->screen_name] = $s->user;

			if($this->lastId < $s->id) $this->lastId = $s->id;
		}
	}

	function loadUser( $path )
	{
		$f = fopen($path, "r");
		while( $line = fgets($f) )
		{
			$a = json_decode($line, true);
			$u = new TwitterUser($a);
			$this->listUser[$u->screen_name] = $u;
		}
		fclose($f);
	}

	function saveUser( $path )
	{
		$f = fopen($path, "w");
		foreach($this->listUser as $u)
		{
			fwrite($f, json_encode($u->toArray(), JSON_UNESCAPED_UNICODE) . "\n");
		}
		fclose($f);
	}
}

class TwitterStatus
{
	const ID = 'id';
	const CREATED_AT = 'created_at';
	const TEXT = 'text';
	const REPLY_TO = 'in_reply_to_status_id';
	const USER = 'user';

	public $id;
	public $created_at;
	public $text;
	public $reply_to;

	public $user;

	function __construct( $a )
	{
		if(is_array($a)) $this->copyArray( $a );
	}

	function copyArray( $a )
	{
		$this->id = $a[self::ID . "_str"] ?  $a[self::ID . "_str"] :  $a[self::ID];	// なるべく文字列として
		if( is_string($a[self::CREATED_AT]) ) $this->created_at = strtotime( $a[self::CREATED_AT] );
		if( is_string($a[self::TEXT]) ) $this->text = $a[self::TEXT];
		if( $a[self::REPLY_TO] ) $this->reply_to = $a[self::REPLY_TO];

		if( $a[self::USER] ) $this->user = new TwitterUser( $a[self::USER] );
	}

	function toArray()
	{
		$created = date("Y-m-d H:i:s", $this->created_at);

		$a = array();
		if( $this->user ) $a[self::USER] = $this->user->toArray();
		$a[self::ID] = (string)$this->id;
		$a[self::CREATED_AT] = $created;
		if($this->reply_to) $a[self::REPLY_TO] = $this->reply_to;
		$a[self::TEXT] = $this->text;
		return $a;
	}
}

class TwitterUser
{
	const ID = 'id';
	const NAME = 'name';
	const SCREEN_NAME = 'screen_name';
	const IMAGE_URL = 'profile_image_url';

	public $id;
	public $name;
	public $screen_name;
	public $image_url;

	function __construct( $a )
	{
		if(is_array($a)) $this->copyArray( $a );
	}

	function copyArray( $a )
	{
		if( is_numeric($a[self::ID]) ) $this->id = $a[self::ID];
		if( $a[self::NAME] ) $this->name = $a[self::NAME];
		if( $a[self::SCREEN_NAME] ) $this->screen_name = $a[self::SCREEN_NAME];
		if( $a[self::IMAGE_URL] ) $this->image_url = $a[self::IMAGE_URL];
	}

	function toArray()
	{
		$a = array();
		$a[self::ID] = $this->id;
		$a[self::NAME] = $this->name;
		$a[self::SCREEN_NAME] = $this->screen_name;
		$a[self::IMAGE_URL] = $this->image_url;
		return $a;
	}
}

?>