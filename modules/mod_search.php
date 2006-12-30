<?php
global $gLibertySystem, $module_rows, $module_params, $module_title;
if( empty( $contentTypes ) ) {
	$contentTypes = array( '' => 'All Content' );
	foreach( $gLibertySystem->mContentTypes as $contentType ) {
		$contentTypes[$contentType["content_type_guid"]] = $contentType["content_description"];
	}
	$gBitSmarty->assign( 'contentTypes', $contentTypes );
}
?>
