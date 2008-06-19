<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_ilike/modules/mod_search.php,v 1.5 2008/06/19 05:12:59 lsces Exp $
 *
 * iLike class
 *
 * @author   xing <xing@synapse.plus.com>
 * @version  $Revision: 1.5 $
 * @package  ilike
 * @subpackage modules
 */

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
