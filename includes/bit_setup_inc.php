<?php
global $gBitSystem;

define( 'LIBERTY_SERVICE_ILIKE', 'search' );

$registerHash = array(
	'package_name' => 'ilike',
	'package_path' => dirname( dirname( __FILE__ ) ).'/',
	'service' => LIBERTY_SERVICE_ILIKE,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'ilike' ) && $gBitUser->hasPermission( 'p_ilike_search' ) ) {
	$menuHash = array(
		'package_name'  => ILIKE_PKG_NAME,
		'index_url'     => ILIKE_PKG_URL.'index.php',
		'menu_template' => 'bitpackage:ilike/menu_ilike.tpl',
		'menu_type' => 'form',
	);
	$gBitSystem->registerAppMenu( $menuHash );

	$gLibertySystem->registerService( LIBERTY_SERVICE_ILIKE, ILIKE_PKG_NAME, array(
		'content_list_sql_function' => 'ilike_content_list_sql',
		'content_search_tpl'		=> 'bitpackage:ilike/search_inc.tpl'
	) );
}
