{if $gBitSystem->isPackageActive( 'ilike' ) and $gBitSystem->isFeatureActive( 'site_header_extended_nav' )}
	<link rel="search" title="{tr}Search{/tr}" href="{$smarty.const.ILIKE_PKG_URL}" />
{/if}
