<?php
require_once( "../../configure.php" );

require_once( INCLUDE_DIR . "web/BaseApi.class.php" );
require_once( INCLUDE_DIR . "learn/VolatileTwitShokos.class.php" );


class TalkApi extends BaseApi
{
	const CACHE_KEY = "talkapi_text";

	function handle()
	{
		$cache = new FileCache();
		$info = $cache->get(self::CACHE_KEY);
		if(!is_array($info))
		{
			$shokos = new VolatileTwitShokos();
			$info = $shokos->bestTalkInfo();
			$cache->set(self::CACHE_KEY, $info, new DateTime("+30 min"));
		}
		$this->assign( 'rate', $info['rate'] );
		$this->assign( 'text', $info['text'] );
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
				<li>しょこすのツイートをします。</li>
				<li>ツイートした後、しばらくキャッシュします。</li>
			</ul>
		</div>
		<div>リクエスト・パラメータ
			<ul>
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

	$api = new TalkApi();
	$api->run();
}
?>