import jQuery from 'jquery';

jQuery.entwine('ss', ($) => {
  /**
   * GridFieldOrderableRows
   */
  $('.ss-gridfield-orderable tbody').entwine({
    rebuildSort() {
      const grid = this.getGridField();

      // Get lowest sort value in this list (respects pagination)
      let minSort = null;
      grid.getItems().each(function () {
        // get sort field
        const sortField = $(this).find('.ss-orderable-hidden-sort');
        if (sortField.length) {
          const thisSort = sortField.val();
          if (minSort === null && thisSort > 0) {
            minSort = thisSort;
          } else if (thisSort > 0) {
            minSort = Math.min(minSort, thisSort);
          }
        }
      });
      minSort = Math.max(1, minSort);

      // With the min sort found, loop through all records and re-arrange
      let sort = minSort;
      grid.getItems().each(function () {
        // get sort field
        const sortField = $(this).find('.ss-orderable-hidden-sort');
        if (sortField.length) {
          sortField.val(sort);
          sort++;
        }
      });
    },
    onadd() {
      const self = this;

      const helper = function (e, row) {
        return row.clone()
              .addClass('ss-gridfield-orderhelper')
              .width('auto')
              .find('.col-buttons')
              .remove()
              .end();
      };

      const update = function (event, ui) {
        // If the item being dragged is unsaved, don't do anything
        let postback = true;
        if ((ui != undefined) && ui.item.hasClass('ss-gridfield-inline-new')) {
          postback = false;
        }

        // Rebuild all sort hidden fields
        self.rebuildSort();

        // Check if we are allowed to postback
        const grid = self.getGridField();
        if (grid.data('immediate-update') && postback) {
          grid.reload({
            url: grid.data('url-reorder')
          });
        } else {
          // Tells the user they have unsaved changes when they
          // try and leave the page after sorting, also updates the
          // save buttons to show the user they've made a change.
          const form = $('.cms-edit-form');
          form.addClass('changed');
        }
      };

      this.sortable({
        handle: '.handle',
        helper,
        opacity: 0.7,
        update
      });
    },
    onremove() {
      if (this.data('sortable')) {
        this.sortable('destroy');
      }
    }
  });

  $('.ss-gridfield-orderable .ss-gridfield-previouspage, .ss-gridfield-orderable .ss-gridfield-nextpage').entwine({
    onadd() {
      const grid = this.getGridField();

      if (this.is(':disabled')) {
        return false;
      }

      const drop = function (e, ui) {
        let page;

        if ($(this).hasClass('ss-gridfield-previouspage')) {
          page = 'prev';
        } else {
          page = 'next';
        }

        grid.find('tbody').sortable('cancel');
        grid.reload({
          url: grid.data('url-movetopage'),
          data: [
            { name: 'move[id]', value: ui.draggable.data('id') },
            { name: 'move[page]', value: page }
          ]
        });
      };

      this.droppable({
        accept: '.ss-gridfield-item',
        activeClass: 'ui-droppable-active ui-state-highlight',
        disabled: this.prop('disabled'),
        drop,
        tolerance: 'pointer'
      });
    },
    onremove() {
      if (this.hasClass('ui-droppable')) this.droppable('destroy');
    }
  });
});
