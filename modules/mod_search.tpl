{strip}
{if $gBitSystem->isPackageActive( 'ilike' )}
	{bitmodule title="$moduleTitle" name="pkg_search_box"}
		{form method="get" ipackage=ilike ifile="index.php"}
			<div class="row">
				<input id="fuser" name="find" size="20" type="text" accesskey="s" value="{tr}Search{/tr}" onfocus="this.value=''" />
				<br />
				{html_options options=$contentTypes name="content_type_guid" selected=$gContent->mContentTypeGuid}
			</div>

			<div class="row submit">
				<input type="submit" name="search" value="{tr}Go{/tr}"/>
			</div>
		{/form}
	{/bitmodule}
{/if}
{/strip}
