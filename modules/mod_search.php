<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_ilike/modules/mod_search.php,v 1.6 2010/04/17 15:36:07 wjames5 Exp $
 *
 * iLike class
 *
 * @author   xing <xing@synapse.plus.com>
 * @version  $Revision: 1.6 $
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
