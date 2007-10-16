<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_ilike/iLike.php,v 1.13 2007/10/16 20:06:22 squareing Exp $
 *
 * iLike class
 *
 * @author   xing <xing@synapse.plus.com>
 * @version  $Revision: 1.13 $
 * @package  pigeonholes
 */

/**
 * iLike
 * @package  ilike
 */
class iLike extends BitBase {

	/**
	* initiate class
	* @return none
	* @access public
	**/
	function iLike() {
		BitBase::BitBase();
	}

	/**
	 * search 
	 * 
	 * @param array $pSearchHash basically the same parameters as a regular list
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function search( &$pSearchHash ) {
		global $gLibertySystem, $gBitSystem, $gBitUser, $gBitDbType;

		// initiate stuff
		BitBase::prepGetList( $pSearchHash );
		$ret = $bindVars = array();
		$selectSql = $whereSql = $orderSql = $joinSql = '';

		// if all content has been selected, there is an empty value in the array
		if( !empty( $pSearchHash['contentTypes'] ) && in_array( '', $pSearchHash['contentTypes'] )) {
			$pSearchHash['contentTypes'] = array();
		}

		// check if the user has the required permissions to view the requested content type
		foreach( $gLibertySystem->mContentTypes as $contentType ) {
			if(( empty( $pSearchHash['contentTypes'] ) || in_array( $contentType["content_type_guid"], $pSearchHash['contentTypes'] )) && $this->hasViewPermission( $contentType["content_type_guid"] )) {
				$allowed[] = $contentType["content_type_guid"];
			}
		}

		if( !empty( $allowed )) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " lc.`content_type_guid` IN( " . implode( ',', array_fill( 0, count( $allowed ), '?' ))." ) ";
			$bindVars = array_merge( $bindVars, $allowed );
		} else {
			$this->mErrors['permission'] = tra( "You don't have the required permissions to search the requested content types." );
		}

		// prepare all the words to search for - allow the use of phrases by enclosing them with "..."
		$find = array();
		$pattern = '#"([^"]*)"#';
		if( preg_match_all( $pattern, $pSearchHash['find'], $matches )) {
			$find = $matches[1];
			// remove the sections we've just dealt with
			$pSearchHash['find'] = preg_replace( $pattern, "", $pSearchHash['find'] );
		}

		// clean up the search words, remove surrounding spaces...
		$pSearchHash['find'] = preg_replace( "!\s+!", " ", trim( $pSearchHash['find'] ));
		if( !empty( $pSearchHash['find'] ) || !empty( $find )) {
			$find = array_merge( $find, explode( ' ', $pSearchHash['find'] ));
		} else {
			$this->mErrors['search'] = tra( "We need a search term for this to work." );
		}

		$findHash = $ignored = array();
		// prepare find hash
		foreach( $find as $key => $val ) {
			if( strlen( $val ) > 2 ) {
				$findHash[] = "%".strtoupper( $val )."%";
			} else {
				$ignored[] = $val;
			}
		}
		// return the list of ignored words
		$pSearchHash['igonred'] = $ignored;

		// here we create the SQL to check for the search words in a given set of columns
		if( !empty( $findHash ) && is_array( $findHash )) {
			// set the list of columns and the required JOINs
			$columns = array( 'lc.`title`', 'lc.`data`', 'lcds.`data`' );
			$whereSql .= empty( $whereSql ) ? ' WHERE( ' : ' AND((';
			$j = 0;
			foreach( $columns as $column ) {
				$i = 0;
				$whereSql .= ( $j == 0 ) ? '' : ')OR( ';
				foreach( $findHash as $val ) {
					$join = !empty( $pSearchHash['join'] ) ? $pSearchHash['join'] : 'AND';
					$whereSql .= ( $i++ > 0 ) ? " $join " : '';
					if( $gBitDbType == "postgres" ) {
						$whereSql .= " $column ILIKE ? ";
					} else {
						$whereSql .= " UPPER( $column ) LIKE ? ";
					}
				}
				$j++;
				$whereSql .= ( $j == count( $columns )) ? ' ) ' : '';
				$bindVars = array_merge( $bindVars, $findHash );
			}
			$whereSql .= ") ";
		} else {
			$this->mErrors['search'] = tra( "The searchterm you entered was probably too short." );
		}

		// get service SQL
		LibertyContent::getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		if( !empty( $pSearchHash['sort_mode'] )) {
			$orderSql = " ORDER BY ".$this->mDb->convertSortmode( $pSearchHash['sort_mode'] );
		}

		// only continue if we haven't choked so far
		if( empty( $this->mErrors )) {
			$query = "
				SELECT lc.`data`, lc.`title`, lcds.`data` AS `summary`, lct.`content_description`, lch.`hits` $selectSql
				FROM `".BIT_DB_PREFIX."liberty_content` lc
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_data` lcds ON ( lc.`content_id` = lcds.`content_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lc.`content_type_guid` = lct.`content_type_guid` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lc.`content_id` = lch.`content_id` )
				$joinSql $whereSql $orderSql";
			$result = $this->mDb->query( $query, $bindVars, $pSearchHash['max_records'], $pSearchHash['offset'] );

			while( $aux = $result->fetchRow() ) {
				$data = $aux['summary']."\n".$aux['data'];
				$aux['len'] = strlen( $data );
				$lines = explode( "\n", strip_tags( $data ));
				foreach( $findHash as $val ) {
					$val = trim( $val, "%" );
					$i = 0;
					foreach( $lines as $number => $line ) {
						if( $i < 3 && !empty( $line ) && stripos( $line, $val ) !== FALSE ) {
							$aux['display_lines'][$number + 1] = encode_email_addresses( $line );
							$i++;
						}
					}

					if( !empty( $aux['display_lines'] )) {
						ksort( $aux['display_lines'] );
					}
				}
				$ret[] = $aux;
			}

			$query = "
				SELECT COUNT( lc.`content_id` )
				FROM `".BIT_DB_PREFIX."liberty_content` lc
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_data` lcds ON ( lc.`content_id` = lcds.`content_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lc.`content_type_guid` = lct.`content_type_guid` )
				$joinSql $whereSql";
			$pSearchHash['cant'] = $this->mDb->getOne( $query, $bindVars );

			BitBase::postGetList( $pSearchHash );
			return $ret;
		} else {
			return FALSE;
		}
	}

	/**
	 * Check to see if a given user has permission to search the selected content type
	 * 
	 * @param string $pContentType 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 * TODO: this is extremely crude and we need a method to get the view permissions from all packages automagically
	 */
	function hasViewPermission( $pContentType = "" ) {
		global $gBitUser;
		$ret = FALSE;
		switch( $pContentType ) {
			case "bitarticle"        : $perm = "p_articles_read";            break;
			case "baords"            : $perm = "p_bitboards_read";           break;
			case "pigeonholes"       : $perm = "p_pigeonholes_view";         break;
			case "treasurygallery"   : $perm = "p_treasury_view_gallery";    break;
			case "treasuryitem"      : $perm = "p_treasury_view_item";       break;
			case "bituser"           : $perm = "p_users_view_user_homepage"; break;
			case "bitpage"           : $perm = "p_wiki_view_page";           break;
			case "bitblogpost"       : $perm = "p_blogs_view";               break;
			case "bitcomment"        : $perm = "p_liberty_read_comments";    break;
			case "fisheyegallery"    : $perm = "p_fisheye_view";             break;
			default                  : $perm = "";                           break;
		}
		return $gBitUser->hasPermission( $perm );
	}
}
?>
