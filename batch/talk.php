<?php
ini_set( 'display_errors', 1 );
require_once( '../configure.php' );
require_once( CONF_DIR . 'common.php' );
require_once( INCLUDE_DIR . "learn/VolatileTwitHajime.class.php" );
require_once( INCLUDE_DIR . "learn/VolatileTwitShokos.class.php" );

//$hajime = new VolatileTwitHajime();
//$hajime->run();
//unset($hajime);

$shokos = new VolatileTwitShokos();
$shokos->run();
unset($shokos);
?>