{strip}
<div class="form-group">
	{formlabel label="Find:" for="ilike-input"}
	{forminput}
		{* use highlight not find to avoid tripping find restrictions in getlist processes *}
		<input name="highlight" id="ilike-input" type="text" accesskey="s" value="{$smarty.request.highlight|escape}"/>
		<br />
		<label><input type="radio" name="join" value="AND" {if !$smarty.request.join || $smarty.request.join == 'AND'}checked="checked"{/if}/> {tr}All words{/tr}</label>
		&nbsp; &nbsp;
		<label><input type="radio" name="join" value="OR" {if $smarty.request.join == 'OR'}checked="checked"{/if}/> {tr}Any word{/tr}</label>
		{formhelp note='Use double quotes to search for phrases. e.g.: "apples and pears"'}
	{/forminput}
</div>
{/strip}
