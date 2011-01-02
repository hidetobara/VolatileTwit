<?php
require_once( "../../configure.php" );
require_once( INCLUDE_DIR . "learn/Ipca.class.php" );
require_once( INCLUDE_DIR . "learn/IpcaImage.class.php" );
require_once( INCLUDE_DIR . "keywords/KeywordAnalyze.class.php" );
require_once( INCLUDE_DIR . "web/BaseApi.class.php" );


class EvaluateApi extends BaseApi
{
	function handle()
	{
		$analyze = new KeywordAnalyze();
		$analyze->loadKeywords( KEYWORD_TABLE );
		$mecab = $analyze->mecab( "今日はいい天気ですね" );
		$mecab = $analyze->addKeywordIndex( $mecab, array('動詞','名詞','形容詞','形容動詞') );
		
		$img = new IpcaImage();
		$img->load_mecab( $mecab );
		$res = new IpcaImage();
		
		$ipca = new Ipca();
		$ipca->load();
		$ipca->project( $img->data, $vec );
		$ipca->backProject( $vec, $res->data, array(1) );
		var_dump($res);
	}
}
$api = new EvaluateApi();
$api->run();
?>