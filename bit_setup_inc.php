<?php
global $gBitSystem;
$registerHash = array(
	'package_name' => 'ilike',
	'package_path' => dirname( __FILE__ ).'/',
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'ilike' )) {
	$menuHash = array(
		'package_name'  => ILIKE_PKG_NAME,
		'index_url'     => ILIKE_PKG_URL.'index.php',
		'menu_template' => 'bitpackage:ilike/menu_ilike.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );
}
?>
