{strip}
<div class="display ilike">
	<div class="header">
		<h1>{tr}Search {if $smarty.request.find}Results{else}Page{/if}{/tr}</h1>
	</div>

	<div class="body">
		{form legend="Extended Search"}
			<div class="row">
				{formlabel label="Limit Search" for="content_type_guid"}
				{forminput}
				{html_checkboxes options=$contentTypes name=contentTypes selected=`$smarty.request.contentTypes` separator="<br />"}
					{formhelp note="Limit search to the selected Liberty package"}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Find" for="find"}
				{forminput}
					<input name="find" size="50" id="find" type="text" accesskey="s" value="{$smarty.request.find|escape}"/>
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" class="wikiaction" name="search" value="{tr}go{/tr}"/>
			</div>
		{/form}

		{if $smarty.request.find}<h2>{tr}Found '<span class="highlight">{$smarty.request.find|escape}</span>' in {$listInfo.total_records|default:0} record(s){/tr}</h2>{/if}

		{formfeedback hash=$feedback}

		<ol>
			{foreach from=$results item=result}
				<li>
					{tr}{$result.content_description}{/tr}: <a href="{$smarty.const.BIT_ROOT_URL}index.php?content_id={$result.content_id}&amp;highlight={$smarty.request.find|escape:url}">{$result.title}</a> &bull; {displayname hash=$result} &bull; {$result.len|kbsize}<br />
					<small>
						{foreach from=$result.display_lines item=line key=number}
							{$number}: &hellip;{$line|truncate:150:"..."}<br />
						{/foreach}
					</small>
				</li>
			{foreachelse}
				{if $smarty.request.find}<div class="norecords">{tr}No pages matched the search criteria{/tr}</div>{/if}
			{/foreach}
		</ol>

		{pagination}
	</div><!-- end .body -->
</div>
{/strip}
