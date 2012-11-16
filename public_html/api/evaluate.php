<?php
require_once( "../../configure.php" );

require_once( INCLUDE_DIR . "web/BaseApi.class.php" );
require_once( INCLUDE_DIR . "learn/VolatileTwitShokos.class.php" );


class EvaluateApi extends BaseApi
{
	const TEXT_LIMIT = 256;

	function handle()
	{
		$text = $_REQUEST['text'];
		if(!$text) $text = "今日の腐さん";

		if( !$text || !is_string($text) ) throw new Exception('No text');
		if( mb_strlen($text) > self::TEXT_LIMIT ) throw new Exception('Too long text');

		$shokos = new VolatileTwitShokos();
		$rate = $shokos->evaluateLikelihood($text);

		$this->assign( 'rate', $rate );
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
				<li>しょこす度を解析します。</li>
				<li>結果は、-1から1までの間です。1でとてもしょこすらしく、-1で全くしょこすらしくない、という判定になります。</li>
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
	Console::$needInfo = false;

	$api = new EvaluateApi();
	$api->run();
}
?>