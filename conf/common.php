<?php
if( ENV_TYPE == 'RELEASE' ){
	define( 'HOME_URL', 'http://baraoto.sakura.ne.jp/VolatileTwit/' );
	define( 'ROOT_DIR', '/home/baraoto/VolatileTwit/' );

	define( 'IPCA_MAIN_BIN', ROOT_DIR . 'data/ipcaMain.bin' );
	define( 'MECAB_EXE', '/usr/local/bin/mecab' );

}else{
	define( 'HOME_URL', 'http://127.0.0.1/VolatileTwit/public_html/' );
	define( 'ROOT_DIR', 'C:/Develop/xampp/htdocs/VolatileTwit/' );

	define( 'IPCA_MAIN_BIN', ROOT_DIR . 'data/ipcaMain.bin' );
	define( 'MECAB_EXE', 'C:/Develop/MeCab/bin/mecab.exe' );

}
define( 'CONF_DIR', ROOT_DIR . 'conf/' );
define( 'INCLUDE_DIR', ROOT_DIR . 'include/' );
define( 'LOG_DIR', ROOT_DIR . 'log/' );
define( 'DATA_DIR', ROOT_DIR . 'data/' );
define( 'TMP_DIR', ROOT_DIR . 'tmp/' );
define( 'SMARTY_WORK_DIR', ROOT_DIR . 'smarty_work/' );
define( 'SMARTY_TEMPLATE_DIR', ROOT_DIR . 'smarty/' );

require_once( CONF_DIR . 'secret.php' );

mb_regex_encoding( 'UTF-8' );
ini_set( 'memory_limit', '512M' );


/*
 * コンソールにテキストを出力する
 */
class Console
{
	static $needInfo = true;

	//info
	static function p( $s )
	{
		if(!self::$needInfo) return;
		print( $s );
	}

	//error
	static function e( $s )
	{
		print( $s );
	}
}
?>