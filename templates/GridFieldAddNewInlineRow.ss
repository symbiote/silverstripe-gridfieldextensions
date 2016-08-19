<script type="text/x-tmpl" class="ss-gridfield-add-inline-template">
	<tr class="ss-gridfield-item ss-gridfield-inline-new">
		<% loop $Me %>
			<% if $IsActions %>
				<td$Attributes>
					<button class="ss-gridfield-delete-inline gridfield-button-delete ss-ui-button" data-icon="cross-circle"></button>
				</td>
			<% else %>
				<td$Attributes>$Content</td>
			<% end_if %>
		<% end_loop %>
	</tr>
</script>
