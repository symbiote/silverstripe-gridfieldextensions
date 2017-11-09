import jQuery from 'jquery';

jQuery.entwine('ss', ($) => {
  /**
   * GridFieldEditableColumns
   */
  $('.grid-field .ss-gridfield-item').entwine({
    onclick(e) {
      // Prevent the default row click action when clicking a cell that contains a field
      if (this.find('.editable-column-field').length) {
        e.stopPropagation();
      }
    }
  });
});
