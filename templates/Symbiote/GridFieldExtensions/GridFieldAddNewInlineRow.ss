<script type="text/x-tmpl" class="ss-gridfield-add-inline-template">
	<tr class="ss-gridfield-item ss-gridfield-inline-new">
		<% loop $Me %>
			<% if $IsActions %>
				<td$Attributes>
					<button class="ss-gridfield-delete-inline gridfield-button-delete action gridfield-button-delete btn--icon-md font-icon-trash-bin btn--no-text grid-field__icon-action form-group--no-label"></button>
				</td>
			<% else %>
				<td$Attributes>$Content</td>
			<% end_if %>
		<% end_loop %>
	</tr>
</script>
