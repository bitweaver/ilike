<?php
global $gLibertySystem, $module_rows, $module_params, $module_title;
if( empty( $contentTypes ) ) {
	foreach( $gLibertySystem->mContentTypes as $cType ) {
		$contentTypes[$cType['content_type_guid']] = $cType['content_description'];
	}
}
asort( $contentTypes );
$contentTypes = array_merge( array( '' => tra( 'All Content' )), $contentTypes );
$gBitSmarty->assign( 'contentTypes', $contentTypes );
?>
