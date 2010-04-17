<?php
// Initialization
require_once( '../kernel/setup_inc.php' );
require_once( ILIKE_PKG_PATH.'iLike.php');

$gBitSystem->verifyPackage( 'ilike' );
$gBitSystem->verifyPermission( 'p_ilike_search' );

$gLike = new iLike();
$feedback = array();

if( empty( $contentTypes ) ) {
	$contentTypes = array( '' => tra( 'All Content' ) );
	foreach( $gLibertySystem->mContentTypes as $cType ) {
		$contentTypes[$cType['content_type_guid']] = $gLibertySystem->getContentTypeName( $cType['content_type_guid'] );
	}
}
$gBitSmarty->assign( 'contentTypes', $contentTypes );

// this is for backward compat with old param contentTypes incase of old bookmarks to search
if( !empty( $_REQUEST['contentTypes'] ) && empty( $_REQUEST['content_type_guid'] ) ){
	$_REQUEST['content_type_guid'] = $_REQUEST['contentTypes'];
	unset($_REQUEST['contentTypes']);
}

$_REQUEST['find'] = !empty( $_REQUEST['highlight'] ) ? $_REQUEST['highlight'] : NULL;
$searchHash = $_REQUEST;
if( !empty( $_REQUEST['find'] ) && $results = $gLike->search( $searchHash ) ) {
	$gBitSmarty->assign( "results", $results );
} elseif( !empty( $_REQUEST['find'] ) ) {
	$feedback['error'] = $gLike->mErrors;
}

// adding contenttype to listInfo is a little complex - this replicates code in liberty::get_content_list_inc.php
if( !empty( $_REQUEST['content_type_guid'] )) {
	if( !is_array( $_REQUEST['content_type_guid'] )) {
		$guids = explode( ",", $_REQUEST['content_type_guid'] );
	} else {
		$guids = $_REQUEST['content_type_guid'];
	}
	$searchHash['listInfo']['ihash']['content_type_guid'] = $guids;
}

// assign so that all form fields are repopulated regardless of whether we have results or not - search services are dependent on this
if( !empty( $searchHash['listInfo'] ) ){
	$gBitSmarty->assign( "listInfo", $searchHash['listInfo'] );
}


$gBitSmarty->assign( "feedback", $feedback );
$gBitSystem->display( 'bitpackage:ilike/search.tpl', tra( 'Search Results' ) , array( 'display_mode' => 'display' ));
?>
