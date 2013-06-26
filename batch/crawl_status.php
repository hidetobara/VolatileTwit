<?php
require_once( '../configure.php' );
require_once( INCLUDE_DIR . "twitter/twitter.class.php" );
require_once( INCLUDE_DIR . "data/FileCache.class.php" );

/*
 * crawl and store twit.
 */
class BatchCrawlStatus
{
	const TWITTER_CRAWL_KEY = "twitter_crawl_key";

	function run()
	{
		$cache = new FileCache();
		$box = $cache->get( self::TWITTER_CRAWL_KEY );
		if( !is_array($box) ) $box = array();

		$since = $box['since'];

		$api = new TwitterApi(HIDETOBARA_OAUTH_KEY, HIDETOBARA_OAUTH_SECRET);
		$a = $api->getHomeTimeline( $since );
		$storage = new TwitterStorage();
		$storage->retrieveStatus($a);
		$storage->saveStatusByDate( LOG_DIR . "status/" );

		$box['since'] = $storage->lastId;
		$cache->set( self::TWITTER_CRAWL_KEY, $box );
	}
}

$crawler = new BatchCrawlStatus();
$crawler->run();
?>