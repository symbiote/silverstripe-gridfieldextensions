<tr>
    <td class="grid-field__paginator bottom-all" colspan="$Colspan">
        <span class="pagination-page-size">
            <%t Symbiote\\GridFieldExtensions\\GridFieldConfigurablePaginator.SHOW 'Show' is 'Verb. Example: Show 1 of 2' %>
            <select name="$PageSizesName" class="pagination-page-size-select no-change-track" data-skip-autofocus="true">
            <% loop $PageSizes %>
                <option <% if $Selected %>selected="selected"<% end_if %>>$Size</option>
            <% end_loop %>
            </select>
            $PageSizesSubmit
        </span>
        <% if not $OnlyOnePage %>
            <div class="grid-field__paginator__controls datagrid-pagination">
                $FirstPage $PreviousPage
                <span class="pagination-page-number">
                    <%t SilverStripe\\Forms\\GridField\\GridFieldPaginator.Page 'Page' %>
                    <input class="text no-change-track" value="$CurrentPageNum" data-skip-autofocus="true" />
                    <%t SilverStripe\\Forms\\GridField\\GridFieldPaginator.OF 'of' is 'Example: View 1 of 2' %>
                    $NumPages
                </span>
                $NextPage $LastPage
            </div>
        <% end_if %>
        <span class="grid-field__paginator_numbers pagination-records-number">
            {$FirstShownRecord}&ndash;{$LastShownRecord}
            <%t SilverStripe\\Forms\\GridField\\GridFieldPaginator.OF 'of' is 'Example: View 1 of 2' %>
            $NumRecords
        </span>
    </td>
</tr>
