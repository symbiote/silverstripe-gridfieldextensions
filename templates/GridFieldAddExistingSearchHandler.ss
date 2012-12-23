$SearchForm

<h3><% _t("RESULTS", "Results") %></h3>
<div class="add-existing-search-results">
	<% if $Items %>
		<ul class="add-existing-search-items" data-add-link="$Link('add')">
			<% loop $Items %>
				<li class="$EvenOdd"><a href="#" data-id="$ID">$Title</a></li>
			<% end_loop %>
		</ul>
	<% else %>
		<p><% _t("NOITEMS", "There are no items.") %></p>
	<% end_if %>

	<% if $Items.MoreThanOnePage %>
		<ul class="add-existing-search-pagination">
			<% if $Items.NotFirstPage %>
				<li><a href="$Items.PrevLink">&laquo;</a></li>
			<% end_if %>

			<% loop $Items.PaginationSummary(4) %>
				<% if $CurrentBool %>
					<li class="current">$PageNum</li>
				<% else_if $Link %>
					<li><a href="$Link">$PageNum</a></li>
				<% else %>
					<li>&hellip;</li>
				<% end_if %>
			<% end_loop %>

			<% if $Items.NotLastPage %>
				<li><a href="$Items.NextLink">&raquo;</a></li>
			<%end_if %>
		</ul>
	<% end_if %>
</div>
