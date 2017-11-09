import jQuery from 'jquery';

jQuery.entwine('ss', ($) => {
  /**
   * GridFieldConfigurablePaginator
   */
  $('.ss-gridfield-configurable-paginator .pagination-page-size-select').entwine({
    onchange() {
      this.parent().find('.ss-gridfield-pagesize-submit').trigger('click');
    }
  });
});
