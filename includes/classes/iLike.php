<?php
/**
 * @version $Header$
 *
 * iLike class
 *
 * @author   xing <xing@synapse.plus.com>
 * @version  $Revision$
 * @package  ilike
 */

/**
 * iLike
 * @package  ilike
 */
class iLike extends BitBase {

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
		if( isset($pSearchHash['content_type_guid']) && !is_array( $pSearchHash['content_type_guid'] )) {
			$pSearchHash['content_type_guid'] = array( $pSearchHash['content_type_guid'] );
		}

		if( !empty( $pSearchHash['content_type_guid'] ) && in_array( '', $pSearchHash['content_type_guid'] )) {
			$pSearchHash['content_type_guid'] = array();
		}

		// check if the user has the required permissions to view the requested content type
		foreach( $gLibertySystem->mContentTypes as $contentType ) {
			if(( empty( $pSearchHash['content_type_guid'] ) || in_array( $contentType["content_type_guid"], $pSearchHash['content_type_guid'] )) && $this->hasViewPermission( $contentType["content_type_guid"] )) {
				$allowed[] = $contentType["content_type_guid"];
			}
		}

		if( in_array( 'bitcomment', $allowed ) ){
			$pSearchHash['include_comments'] = TRUE;
		}

		if( !empty( $allowed )) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " lc.`content_type_guid` IN( " . implode( ',', array_fill( 0, count( $allowed ), '?' ))." ) ";
			$bindVars = array_merge( $bindVars, $allowed );
		} else {
			$this->mErrors['permission'] = tra( "You don't have the required permissions to search the requested content types." );
		}

		// create valid search SQL
		if( $errors = $this->prepareSearchSql( $pSearchHash, $whereSql, $bindVars )) {
			$this->mErrors = $errors;
		}

		// get service SQL
		self::getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, NULL, $pSearchHash );

		if( !empty( $pSearchHash['sort_mode'] )) {
			$orderSql = " ORDER BY lc.".$this->mDb->convertSortmode( $pSearchHash['sort_mode'] );
		}

		// only continue if we haven't choked so far
		if( empty( $this->mErrors )) {
			$query = "
				SELECT 
				uue.`login` AS `modifier_user`,
				uue.`real_name` AS `modifier_real_name`,
				uue.`user_id` AS `modifier_user_id`,
				uuc.`login` AS `creator_user`,
				uuc.`real_name` AS `creator_real_name`,
				uuc.`user_id` AS `creator_user_id`,
				lc.`data`, 
				lc.`content_id`, 
				lc.`title`, 
				lcds.`data` AS `summary`, 
				lct.`content_name`, 
				lct.`content_name_plural`, 
				lch.`hits`,  
				lc.`last_modified`,
				lc.`created`,
				lc.`content_type_guid`
				$selectSql
				FROM `".BIT_DB_PREFIX."liberty_content` lc
					INNER JOIN `".BIT_DB_PREFIX."users_users` uuc ON (lc.`user_id`=uuc.`user_id`)
					INNER JOIN `".BIT_DB_PREFIX."users_users` uue ON (lc.`modifier_user_id`=uue.`user_id`)
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_data` lcds ON ( lc.`content_id` = lcds.`content_id` AND lcds.`data_type` = 'summary' )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lc.`content_type_guid` = lct.`content_type_guid` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lc.`content_id` = lch.`content_id` )
				$joinSql $whereSql $orderSql";

			$result = $this->mDb->query( $query, $bindVars, $pSearchHash['max_records'], $pSearchHash['offset'] );

			while( $aux = $result->fetchRow() ) {
				$data = $aux['summary']."\n".$aux['data'];
				$aux['len'] = strlen( $data );
				$lines = explode( "\n", strip_tags( $data ));
				foreach( $pSearchHash['findHash'] as $val ) {
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
				$aux['display_url'] = BIT_ROOT_URL."index.php?content_id=".$aux['content_id'];
				$ret[] = $aux;
			}

			// do some custom sorting
			usort( $ret, 'ilike_relevance_sort' );

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
	* Set up SQL strings for services used by the object
	* TODO: set this function deprecated and eventually nuke it
	*/
	static protected function getServicesSql( $pServiceFunction, &$pSelectSql, &$pJoinSql, &$pWhereSql, &$pBindVars, $pObject = NULL, &$pParamHash = NULL ) {
		//deprecated( 'You package is calling the deprecated LibertyContent::getServicesSql() method. Please update your code to use LibertyContent::getLibertySql' );
		global $gLibertySystem;
		if( $loadFuncs = $gLibertySystem->getServiceValues( $pServiceFunction ) ) {
			foreach( $loadFuncs as $func ) {
				if( function_exists( $func ) ) {
					if( !empty( $pObject ) && is_object( $pObject ) ) {
						$loadHash = $func( $pObject, $pParamHash );
					} else {
						$loadHash = $func( (!empty( $pObject ) ? $this : NULL), $pParamHash );
					}
					if( !empty( $loadHash['select_sql'] ) ) {
						$pSelectSql .= $loadHash['select_sql'];
					}
					if( !empty( $loadHash['join_sql'] ) ) {
						$pJoinSql .= $loadHash['join_sql'];
					}
					if( !empty( $loadHash['where_sql'] ) ) {
						$pWhereSql .= $loadHash['where_sql'];
					}
					if( !empty( $loadHash['bind_vars'] ) ) {
						if ( is_array( $pBindVars ) ) {
							$pBindVars = array_merge( $pBindVars, $loadHash['bind_vars'] );
						} else {
							$pBindVars = $loadHash['bind_vars'];
						}
					}
				}
			}
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
		global $gBitUser, $gLibertySystem, $gBitSystem;
		static $sPermObjects;

		if( empty( $sPermObjects[$pContentType]['content_object'] )) {
			// create *one* object for each object *type* to  call virtual methods.
			if( $typeClass = $gLibertySystem->getContentClassName( $pContentType ) ) {
				$sPermObjects[$pContentType]['content_object'] = new $typeClass();
			}
		}

		// check to see if the user has the required permissions to view this content type
		if( !empty( $sPermObjects[$pContentType]['content_object'] )) {
			return $sPermObjects[$pContentType]['content_object']->hasViewPermission();
		} else {
			return TRUE;
		}
	}

	/**
	 * prepareSearchSql 
	 * 
	 * @param array $pSearchHash 
	 * @param boolean $pIsService 
	 * @access public
	 * @return boolean TRUE on success, FALSE on failure - $this->mErrors will contain reason for failure
	 */
	static function prepareSearchSql( &$pSearchHash, &$pWhereSql, &$pBindVars, $pIsService = FALSE ) {
		global $gBitDbType;
		$errors = FALSE;

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
			$errors['search'] = tra( "We need a search term for this to work." );
		}

		$pSearchHash['findHash'] = $ignored = array();
		// prepare find hash
		foreach( $find as $key => $val ) {
			if( strlen( $val ) > 2 ) {
				$pSearchHash['findHash'][] = "%".strtoupper( str_replace( "%", "\%", $val ))."%";
			} else {
				$ignored[] = $val;
			}
		}
		// return the list of ignored words
		$pSearchHash['igonred'] = $ignored;

		// here we create the SQL to check for the search words in a given set of columns
		if( !empty( $pSearchHash['findHash'] ) && is_array( $pSearchHash['findHash'] )) {
			// set the list of columns and the required JOINs
			if( $pIsService ) {
				$columns = array( 'lc.`title`', 'lc.`data`', 'ilikelcds.`data`' );
				$pWhereSql .= ' AND((';
			} else {
				$columns = array( 'lc.`title`', 'lc.`data`', 'lcds.`data`' );
				$pWhereSql .= empty( $pWhereSql ) ? ' WHERE( ' : ' AND((';
			}

			$j = 0;
			foreach( $columns as $column ) {
				$i = 0;
				$pWhereSql .= ( $j == 0 ) ? '' : ')OR( ';
				foreach( $pSearchHash['findHash'] as $val ) {
					$join = !empty( $pSearchHash['join'] ) ? $pSearchHash['join'] : 'AND';
					$pWhereSql .= ( $i++ > 0 ) ? " $join " : '';
					if( $gBitDbType == "postgres" ) {
						$pWhereSql .= " $column ILIKE ? ";
					} else {
						$pWhereSql .= " UPPER( $column ) LIKE ? ";
					}
				}
				$j++;
				$pWhereSql .= ( $j == count( $columns )) ? ' ) ' : '';
				$pBindVars = array_merge( $pBindVars, $pSearchHash['findHash'] );
			}
			$pWhereSql .= ") ";
		} else {
			$errors['search'] = tra( "The searchterm you entered was probably too short." );
		}

		return $errors;
	}
}

/**
 * ilike_relevance_sort usort callback function to increase relevance of result if search result is in title
 * 
 * @param array $pHash Hash of search results
 * @access public
 * @return -1 if result is in title, 1 otherwise
 */
function ilike_relevance_sort( $pHash ) {
	if( !empty( $_REQUEST['find'] )) {
		$find = explode( ' ', preg_replace( "!\s+!", " ", trim( $_REQUEST['find'] )));
		if( is_array( $find )) {
			foreach( $find as $word ) {
				if( !preg_match( "#$word#i", $pHash['title'] )) {
					return 1;
				}
			}
		}
	}
}

/**
 * ilike_content_list_sql 
 * 
 * @param array $pObject 
 * @param array $pParamHash 
 * @access public
 * @return boolean TRUE on success, FALSE on failure - $this->mErrors will contain reason for failure
 */
function ilike_content_list_sql( $pObject, &$pParamHash=NULL ) {
	global $gBitSystem, $gBitDbType;
	$ret = array();

	if( !empty( $pParamHash['highlight'] )) {

		$pSearchHash = $pParamHash;
		$pSearchHash['find'] = $pSearchHash['highlight'];
		$selectSql = $whereSql = $orderSql = $joinSql = '';
		$bindVars = array();

		// create valid search SQL
		iLike::prepareSearchSql( $pSearchHash, $whereSql, $bindVars, TRUE );

		$ret['join_sql'] = " LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_data` ilikelcds ON ( lc.`content_id` = ilikelcds.`content_id` AND ilikelcds.`data_type` = 'summary' ) ";
		$ret['where_sql'] = $whereSql;
		$ret['bind_vars'] = $bindVars;

		if( !empty( $pParamHash['highlight'] ) ){
			$pParamHash['listInfo']['highlight'] = $pParamHash['highlight'];
			$pParamHash['listInfo']['ihash']['highlight'] = $pParamHash['highlight'];
		}

		if( !empty( $pParamHash['join'] ) ){
			$pParamHash['listInfo']['join'] = $pParamHash['join'];
			$pParamHash['listInfo']['ihash']['join'] = $pParamHash['join'];
		}
	}

	return $ret;
}
?>
