<?php
if( ENV_TYPE == 'RELEASE' ){
	define( 'HOME_URL', 'http://baraoto.sakura.ne.jp/VolatileTwit/' );
	define( 'ROOT_DIR', '/home/baraoto/VolatileTwit-trunk/' );

}else{
	define( 'HOME_URL', 'http://127.0.0.1/VolatileTwit/public_html/' );
	define( 'ROOT_DIR', 'C:/Develop/xampp/htdocs/VolatileTwit/' );
}
define( 'CONF_DIR', ROOT_DIR . 'conf/' );
define( 'INCLUDE_DIR', ROOT_DIR . 'include/' );
define( 'LOG_DIR', ROOT_DIR . 'log/' );
define( 'DATA_DIR', ROOT_DIR . 'data/' );
define( 'SMARTY_WORK_DIR', ROOT_DIR . 'smarty_work/' );
define( 'SMARTY_TEMPLATE_DIR', ROOT_DIR . 'smarty/' );

mb_regex_encoding( "UTF-8" );

require_once( CONF_DIR . 'cecret.php' );

/*
 * load local configure file.
 */
function loadLocalConf( $name )
{
	$path = CONF_DIR . $name;
	if( is_file($path) ) require_once( $path );
	require( CONF_DIR . 'local.php' );
}
?>