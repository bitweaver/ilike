<?php
/**
 * @version $Header$
 *
 * iLike class
 *
 * @author   xing <xing@synapse.plus.com>
 * @version  $Revision$
 * @package  ilike
 * @subpackage modules
 */

global $gLibertySystem, $module_rows, $module_params, $module_title;
if( empty( $contentTypes ) ) {
	foreach( $gLibertySystem->mContentTypes as $cType ) {
		$contentTypes[$cType['content_type_guid']] = $gLibertySystem->getContentTypeName( $cType['content_type_guid'] );
	}
}
asort( $contentTypes );
$contentTypes = array_merge( array( '' => tra( 'All Content' )), $contentTypes );
$gBitSmarty->assign( 'contentTypes', $contentTypes );
?>
