<tr>
    <td class="bottom-all" colspan="$Colspan">
        <span class="pagination-page-size">
            <%t GridFieldConfigurablePaginator.SHOW 'Show' %>
            <select name="$PageSizesName" class="pagination-page-size-select" data-skip-autofocus="true">
            <% loop $PageSizes %>
                <option <% if $Selected %>selected="selected"<% end_if %>>$Size</option>
            <% end_loop %>
            </select>
            $PageSizesSubmit
        </span>
        <% if not $OnlyOnePage %>
            <div class="datagrid-pagination">
                $FirstPage $PreviousPage
                <span class="pagination-page-number">
                    <%t Pagination.Page 'Page' %>
                    <input class="text" value="$CurrentPageNum" data-skip-autofocus="true" />
                    <%t TableListField_PageControls_ss.OF 'of' is 'Example: View 1 of 2' %>
                    $NumPages
                </span>
                $NextPage $LastPage
            </div>
        <% end_if %>
        <span class="pagination-records-number">
            {$FirstShownRecord}&ndash;{$LastShownRecord}
            <%t TableListField_PageControls_ss.OF 'of' is 'Example: View 1 of 2' %>
            $NumRecords
        </span>
    </td>
</tr>
