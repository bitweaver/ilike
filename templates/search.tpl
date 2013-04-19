{strip}
<div class="display ilike">
	<header>
		<h1>{if $smarty.request.find}{tr}Search results{/tr}{else}{tr}Search{/tr}{/if}</h1>
	</header>

	<div class="row-fluid">
		{if $smarty.request.find}
		<div class="span9 pull-left">
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
		</div>
		{/if}
		<div class="span3 pull-left">
		{form method="get" action=$smarty.const.ILIKE_PKG_URL}
			<div class="control-group">
				{formlabel label="Find" for="ilike-input"}
				{forminput}
					<input name="highlight" size="50" id="ilike-input" type="text" accesskey="s" value="{$smarty.request.find|escape}"/>
					{formhelp note='Use double quotes to search for phrases. e.g.: "apple computer"'}
					<label class="radio"><input type="radio" name="join" value="AND" {if !$smarty.request.join || $smarty.request.join == 'AND'}checked="checked"{/if}/> {tr}All words{/tr}</label>
					<label class="radio"><input type="radio" name="join" value="OR" {if $smarty.request.join == 'OR'}checked="checked"{/if}/> {tr}Any word{/tr}</label>
				{/forminput}
			</div>

			<div class="control-group">
				<label class="checkbox"><input name="content_limit" type="checkbox" {if !empty($smarty.request.content_type_guid)}checked="checked"{/if} onclick="$('#contentlimit').toggle();">{tr}Limit search to types{/tr}</label>
				<div class="well" id="contentlimit" {if empty($smarty.request.content_type_guid)}style="display:none;"{/if}>
					{html_checkboxes options=$contentTypes name=content_type_guid selected=$smarty.request.content_type_guid}
				</div>
			</div>

			<div class="control-group submit">
				<input type="submit" class="btn btn-primary" name="search" value="{tr}Search{/tr}"/>
			</div>

		{/form}
		</div>
	</div><!-- end .body -->
</div><!-- end .ilike -->
{/strip}
