<?php
// Initialization
require_once( '../bit_setup_inc.php' );
require_once( ILIKE_PKG_PATH.'iLike.php');

$gBitSystem->verifyPackage( 'ilike' );
$gBitSystem->verifyPermission( 'p_ilike_search' );

$gLike = new iLike();
$feedback = array();

if( empty( $iTypes ) ) {
	$iTypes = array( '' => tra( 'All Content' ) );
	foreach( $gLibertySystem->mContentTypes as $cType ) {
		$iTypes[$cType['content_type_guid']] = $cType['content_description'];
	}
}
$gBitSmarty->assign( 'iTypes', $iTypes );

// if we are searching with the search box, we are handed a single content type
if( !empty( $_REQUEST['content_type_guid'] ) ) {
	$_REQUEST['iTypes'][] = $_REQUEST['content_type_guid'];
}

$_REQUEST['find'] = !empty( $_REQUEST['highlight'] ) ? $_REQUEST['highlight'] : NULL;
$searchHash = $_REQUEST;
if( !empty( $_REQUEST['find'] ) && $results = $gLike->search( $searchHash ) ) {
	$gBitSmarty->assign( "results", $results );
	$gBitSmarty->assign( "listInfo", $searchHash['listInfo'] );
} elseif( !empty( $_REQUEST['find'] ) ) {
	$feedback['error'] = $gLike->mErrors;
}

$gBitSmarty->assign( "feedback", $feedback );
$gBitSystem->display( 'bitpackage:ilike/search.tpl', tra( 'Search Results' ) );
?>
