<?php
require_once( "../../configure.php" );

require_once( INCLUDE_DIR . "web/BaseApi.class.php" );
require_once( INCLUDE_DIR . "keywords/KeywordAnalyze.class.php" );
require_once( INCLUDE_DIR . "keywords/mecab.function.php" );
require_once( INCLUDE_DIR . "learn/Ipca.class.php" );
require_once( INCLUDE_DIR . "learn/IpcaImage.class.php" );



class EvaluateApi extends BaseApi
{
	const TEXT_LIMIT = 256;
	const RATING_BIAS = 10.0;
	
	function handle()
	{
		$text = $_REQUEST['text'];
		//$text = "飲み会のあとはハーゲンダッツとか食べたくなるな";
		//$text = "言語の仕様が巨大だと、プログラムを書く分には一部の機能を使わなきゃいいだけなので困らないが、読む分には困る";
		
		if( !$text || !is_string($text) ) throw new Exception('No text');
		if( mb_strlen($text) > self::TEXT_LIMIT ) throw new Exception('Too long text');
		
		$analyze = new KeywordAnalyze();
		$analyze->loadKeywords( KEYWORD_LIST );
		$mecab = mecab( $text );
		$mecab = $analyze->addKeywordIndex( $mecab, array('動詞','名詞','形容詞','形容動詞') );
		
		$filter = new IpcaImage();
		$filter->load_1Line1Element( FILTER_LIST, 0, 1 );
		
		$img = new IpcaImage();
		$img->load_mecab( $mecab );
		$img->mul( $filter );

		$res = new IpcaImage();
		$ipca = new Ipca();
		
//		$ipca->load();
//		$ipca->project( $img->data, $vec );
//		$ipca->backProject( $vec, $res->data, array(0,1,2) );	var_dump($res->data);

		$ipca->load(1);
		$ipca->reflectProject( $img->data, $res->data, 1 );
		
		$rating = $res->data[1] * self::RATING_BIAS;
		if( $rating > 1.0 ) $rating = 1.0;
		if( $rating < -1.0 ) $rating = -1.0;
		$this->assign( 'rating', $rating );
		$this->assign( 'text', htmlspecialchars($text) );
		$this->assign( 'status', 'ok' );
	}
}	

if( $_REQUEST['help'] )
{
?>
	<html>
	<body>
		<div>概要
			<ul>
				<li>星一度を解析します。</li>
				<li>結果は、-1から1までの間です。1でとても星一らしく、-1で全く星一らしくない、という判定になります。</li>
				<li>一回の計算に数秒かかります。</li>
			</ul>
		</div>
		<div>リクエスト・パラメータ
			<ul>
				<li>text: 解析する文字。</li>
				<li>format: 解析結果のフォーマット。json, xml, txt が可能。</li>
				<li>help: ヘルプを表示します。</li>
			</ul>
		</div>
	</body>
	</html>
<?php
}
else
{
	$api = new EvaluateApi();
	$api->run();
}
?>