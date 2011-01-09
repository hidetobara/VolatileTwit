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
		$analyze->loadKeywords( KEYWORD_LIST );
		$mecab = $analyze->mecab( "飲み会のあとはハーゲンダッツとか食べたくなるな" );
//		$mecab = $analyze->mecab( "言語の仕様が巨大だと、プログラムを書く分には一部の機能を使わなきゃいいだけなので困らないが、読む分には困る" );
		$mecab = $analyze->addKeywordIndex( $mecab, array('動詞','名詞','形容詞','形容動詞') );
		
		$filter = new IpcaImage();
		$filter->load_1Line1Element( FILTER_LIST, 0, 1 );	//var_dump(array($filter->data[0],$filter->data[217]));
		
		$img = new IpcaImage();
		$img->load_mecab( $mecab );
		$img->mul( $filter );
		
		$ipca = new Ipca();
		$ipca->load();
		$ipca->project( $img->data, $vec );
		$res = new IpcaImage();
		$ipca->backProject( $vec, $res->data, array(1) );	var_dump($res->data);
	}
}
$api = new EvaluateApi();
$api->run();
?>