<button
	class="btn btn-secondary btn--no-text btn--icon-large <% if $Toggle == 'open' %>font-icon-down-dir<% else %>font-icon-right-dir<% end_if %> cms-panel-link list-children-link"
	aria-expanded="<% if $Toggle == 'open' %>true<% else %>false<% end_if %>"
	data-pjax-target="$PjaxFragment"
	data-url="$Link"
	data-toggle="$ToggleLink"
></button>
<% if $Toggle == 'open' %>
	$NestedField
<% else %>
	<div class="nested-container" data-pjax-fragment="$PjaxFragment" style="display:none;"></div>
<% end_if %>