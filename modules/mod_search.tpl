{strip}
{if $gBitSystem->isPackageActive( 'ilike' )}
	{bitmodule title="$moduleTitle" name="ilike_box"}
		{form method="get" ipackage=ilike ifile="index.php"}
			<div class="row">
				<input id="fuser" name="highlight" size="15" type="text" accesskey="s" value="{tr}Search{/tr}" onblur="if (this.value == '') {ldelim}this.value = '{tr}Search{/tr}';{rdelim}" onfocus="if (this.value == '{tr}Search{/tr}') {ldelim}this.value = '';{rdelim}" />
				<br />
				{html_options options=$contentTypes name="content_type_guid" selected=$gContent->mContentTypeGuid}
			</div>

			<div class="row submit">
				<input type="submit" name="search" value="{tr}Go{/tr}" />
			</div>
		{/form}
	{/bitmodule}
{/if}
{/strip}
