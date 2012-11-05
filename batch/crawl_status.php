<?php
require_once( '../configure.php' );
require_once( INCLUDE_DIR . "twitter/twitter.class.php" );
require_once( INCLUDE_DIR . "data/FileCache.class.php" );

/*
 * crawl and store twit.
 */
class BatchCrawlStatus
{
	const URL_GET_TIMELINE = 'http://api.twitter.com/1/statuses/friends_timeline.xml';
	const NAME_LAST_ID = 'crawl_status_last_id';
	const GET_STATUS_MAX = 200;

	protected $cache;
	protected $oauth;

	function __construct()
	{
		$this->cache = new FileCache();
		$this->oauth = new TwitterOAuth(
			CONSUMER_KEY,
			CONSUMER_SECRET,
			HIDETOBARA_OAUTH_KEY,
			HIDETOBARA_OAUTH_SECRET
			);
	}

	function run()
	{
		$response = $this->getRecentStatus();

		$object = $this->cache->get( self::NAME_LAST_ID );
		$storage = new TwitterStorage();
		$storage->setState( $object );
		$storage->loadUserFromFile();
		$storage->retrieveStatusFromXml( $response );
		$storage->saveStatus();
		$storage->saveUser();

		$this->cache->set( self::NAME_LAST_ID, $storage->getState() );
	}

	function getRecentStatus()
	{
		$options = array( 'count' => self::GET_STATUS_MAX );
		$response = $this->oauth->get( self::URL_GET_TIMELINE, $options	);
		var_dump( $response );
		return $response;
	}
}

$crawler = new BatchCrawlStatus();
$crawler->run();
?>