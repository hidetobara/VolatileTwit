<?php
require_once( "../../configure.php" );

require_once( INCLUDE_DIR . "web/BaseApi.class.php" );
require_once( INCLUDE_DIR . "learn/TalkManager.class.php" );


class TalkApi extends BaseApi
{
	function handle()
	{		
		$talker = new TalkManager();
		$best = $talker->bestTalk();
		
		$this->assign( 'text', $best['text'] );
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
				<li>星一のつぶやきをします。</li>
				<li>負荷対策のため、つぶやきの結果は３０分キャッシュします。</li>
			</ul>
		</div>
		<div>リクエスト・パラメータ
			<ul>
				<li>help: ヘルプを表示します。</li>
			</ul>
		</div>
	</body>
	</html>
<?php
}
else
{
	$api = new TalkApi();
	$api->run();
}
?>