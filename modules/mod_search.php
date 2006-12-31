<?php
global $gLibertySystem, $module_rows, $module_params, $module_title;
if( empty( $contentTypes ) ) {
	$contentTypes = array( '' => tra( 'All Content' ) );
	foreach( $gLibertySystem->mContentTypes as $cType ) {
		$contentTypes[$cType['content_type_guid']] = tra( $cType['content_description'] );
	}
	$gBitSmarty->assign( 'contentTypes', $contentTypes );
}
?>
