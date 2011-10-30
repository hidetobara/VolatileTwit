<?php
ini_set( 'display_errors', 1 );
require_once( '../configure.php' );
require_once( INCLUDE_DIR . "learn/VolatileTwitHajime.class.php" );

$hajime = new VolatileTwitHajime();
$hajime->run();
?>