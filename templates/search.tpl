{strip}
<div class="display ilike">
	<div class="header">
		<h1>
			{if $smarty.request.find}
				{tr}Search results{/tr}
			{else}
				{tr}Search{/tr}
			{/if}
		</h1>
	</div>

	<div class="body">
		{form method="get" action=$smarty.const.ILIKE_PKG_URL}
			<div class="row">
				{formlabel label="Find" for="ilike-input"}
				{forminput}
					<input name="highlight" size="50" id="ilike-input" type="text" accesskey="s" value="{$smarty.request.find|escape}"/>
					<br />
					<label><input type="radio" name="join" value="AND" {if !$smarty.request.join || $smarty.request.join == 'AND'}checked="checked"{/if}/> {tr}All words{/tr}</label>
					&nbsp; &nbsp;
					<label><input type="radio" name="join" value="OR" {if $smarty.request.join == 'OR'}checked="checked"{/if}/> {tr}Any word{/tr}</label>
					{formhelp note='Use double quotes to search for phrases. e.g.: "apples and pears"'}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" class="wikiaction" name="search" value="{tr}go{/tr}"/>
			</div>

			<div class="row">
				{formlabel label="Limit Search"}
				{forminput}
				{html_checkboxes options=$contentTypes name=content_type_guid selected=`$smarty.request.content_type_guid` separator="&nbsp; &nbsp; "}
					{formhelp note="Limit search to the selected content types"}
				{/forminput}
			</div>
		{/form}

		{if $smarty.request.find}
			<hr />

			<h2>{tr}Found '<span class="highlight">{$smarty.request.find|escape}</span>' in {$listInfo.total_records|default:0} record(s){/tr}</h2>

			{formfeedback hash=$feedback}

			<ol>
				{foreach from=$results item=result}
					<li>
						{tr}{$result.content_name}{/tr}: <a href="{$result.display_url}{if $result.content_type_guid != 'bitcomment'}&amp;highlight={$smarty.request.find|escape:url}{/if}">{if $result.title}{$result.title|escape}{else}[ no title ]{/if}</a> <small>{$result.len|display_bytes}</small><br />
						<small>
							{foreach from=$result.display_lines item=line key=number}
								{$number}: {$line|truncate:125:"&hellip;"}<br />
							{/foreach}
						</small>
					</li>
				{foreachelse}
					<div class="norecords">{tr}No pages matched the search criteria{/tr}</div>
				{/foreach}
			</ol>

			{pagination highlight=$smarty.request.highlight join=$smarty.request.join}

			<hr />
		{/if}
	</div><!-- end .body -->
</div><!-- end .ilike -->
{/strip}
