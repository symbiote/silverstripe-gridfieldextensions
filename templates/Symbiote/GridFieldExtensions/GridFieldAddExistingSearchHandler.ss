$SearchForm

<h3><%t GridFieldExtensions.RESULTS "Results" %></h3>
<div class="add-existing-search-results">
	<% if $Items %>
		<ul class="list-group add-existing-search-items" data-add-link="$Link('add')">
			<% loop $Items %>
				<li class="$EvenOdd list-group-item list-group-item-action">
                    <a href="#" data-id="$ID">$Title</a>
                </li>
			<% end_loop %>
		</ul>
	<% else %>
		<p><%t GridFieldExtensions.NOITEMS "There are no items." %></p>
	<% end_if %>

	<% if $Items.MoreThanOnePage %>
		<ul class="pagination add-existing-search-pagination">
			<% if $Items.NotFirstPage %>
				<li class="page-item">
                    <a class="page-link" href="$Items.PrevLink">
                        <span aria-hidden="true">&laquo;</span>
                        <span class="sr-only"><%t GridFieldExtensions.PREVIOUS "Previous" %></span>
                    </a>
                </li>
			<% end_if %>

			<% loop $Items.PaginationSummary(2) %>
				<% if $CurrentBool %>
					<li class="page-item active">
                        <a class="page-link" href="#">
                            $PageNum <span class="sr-only"><%t GridFieldExtensions.CURRENT "(current)" %></span>
                        </a>
                    </li>
				<% else_if $Link %>
                    <li class="page-item">
                        <a class="page-link" href="$Link">
                            $PageNum
                        </a>
                    </li>
				<% else %>
					<li class="page-item disabled">
                        <a class="page-link" href="#">&hellip;</a>
                    </li>
				<% end_if %>
			<% end_loop %>

			<% if $Items.NotLastPage %>
                <a class="page-link" href="$Items.NextLink">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only"><%t GridFieldExtensions.Next "Next" %></span>
                </a>
			<%end_if %>
		</ul>
	<% end_if %>
</div>
