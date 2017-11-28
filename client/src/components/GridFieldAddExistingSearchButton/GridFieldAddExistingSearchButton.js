import jQuery from 'jquery';

jQuery.entwine('ss', ($) => {
  /**
   * GridFieldAddExistingSearchButton
   */
  $('.add-existing-search-dialog').entwine({
    loadDialog(deferred) {
      const dialog = this.addClass('loading').children('.ui-dialog-content').empty();

      deferred.done((data) => {
        dialog.html(data).parent().removeClass('loading');
      });
    }
  });

  $('.ss-gridfield .add-existing-search').entwine({
    onclick() {
      const dialog = $('<div></div>').appendTo('body').dialog({
        modal: true,
        resizable: false,
        width: 500,
        height: 600,
        close() {
          $(this).dialog('destroy').remove();
        }
      });

      dialog.parent().addClass('add-existing-search-dialog').loadDialog(
        $.get(this.prop('href'))
      );
      dialog.data('grid', this.closest('.ss-gridfield'));

      return false;
    }
  });

  $('.add-existing-search-dialog .add-existing-search-form').entwine({
    onsubmit() {
      this.closest('.add-existing-search-dialog').loadDialog($.get(
        this.prop('action'), this.serialize()
      ));
      return false;
    }
  });

  $('.add-existing-search-dialog .add-existing-search-items a').entwine({
    onclick() {
      const link = this.closest('.add-existing-search-items').data('add-link');
      const id = this.data('id');

      const dialog = this.closest('.add-existing-search-dialog')
       .addClass('loading')
       .children('.ui-dialog-content')
       .empty();

      $.post(link, { id }, () => {
        dialog.data('grid').reload();
        dialog.dialog('close');
      });

      return false;
    }
  });

  $('.add-existing-search-dialog .add-existing-search-pagination a').entwine({
    onclick() {
      this.closest('.add-existing-search-dialog').loadDialog($.get(
        this.prop('href')
      ));
      return false;
    }
  });
});
