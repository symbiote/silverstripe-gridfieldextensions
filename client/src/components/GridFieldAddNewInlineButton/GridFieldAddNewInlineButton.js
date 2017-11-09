/* global window */

import jQuery from 'jquery';
import i18n from 'i18n';

jQuery.entwine('ss', ($) => {
  /**
   * GridFieldAddNewInlineButton
   */
  $('.ss-gridfield.ss-gridfield-editable').entwine({
    reload(opts, success) {
      const grid = this;
      // Record position of all items
      const added = [];
      let index = 0; // 0-based index
      grid.find('tbody:first .ss-gridfield-item').each(function () {
        // Record inline items with their original positions
        if ($(this).is('.ss-gridfield-inline-new')) {
          added.push({
            index,
            row: $(this).detach()
          });
        }
        index++;
      });

      this._super(opts, function () {
        const body = grid.find('tbody:first');
        $.each(added, (i, item) => {
          let row = item.row,
            index = item.index,
            replaces;
          // Insert at index position
          if (index === 0) {
            body.prepend(row);
          } else {
            // Find item that we could potentially insert this row after
            replaces = body.find(`.ss-gridfield-item:nth-child(${index})`);
            if (replaces.length) {
              replaces.after(row);
            } else {
              body.append(row);
            }
          }
          grid.find('tbody:first').children('.ss-gridfield-no-items').hide();
        });

        if (success) success.apply(grid, arguments);
      });
    },
    onpaste(e) {
      // The following was used as a basis for clipboard data access:
      // http://stackoverflow.com/questions/2176861/javascript-get-clipboard-data-on-paste-event-cross-browser
      const clipboardData = typeof e.originalEvent.clipboardData !== 'undefined' ? e.originalEvent.clipboardData : null;
      if (clipboardData) {
        // Get current input wrapper div class (ie. 'col-Title')
        const input = $(e.target);
        const inputType = input.attr('type');
        if (inputType === 'text' || inputType === 'email') {
          const lastInput = this.find('.ss-gridfield-inline-new:last').find('input');
          if (input.attr('type') === 'text' && input.is(lastInput) && input.val() === '') {
            const inputWrapperDivClass = input.parent().attr('class');
            // Split clipboard data into lines
            const lines = clipboardData.getData('text/plain').match(/[^\r\n]+/g);
            const linesLength = lines.length;
            // If there are multiple newlines detected, split the data into new rows automatically
            if (linesLength > 1) {
              const elementsChanged = [];
              for (let i = 1; i < linesLength; ++i) {
                this.trigger('addnewinline');
                const row = this.find('.ss-gridfield-inline-new:last');
                const rowInput = row.find(`.${inputWrapperDivClass}`).find('input');
                rowInput.val(lines[i]);
                elementsChanged.push(rowInput);
              }
              // Store the rows added via this method so they can be undo'd.
              input.data('pasteManipulatedElements', elementsChanged);
              // To set the current row to not just be all the clipboard data, must wait a frame
              setTimeout(() => {
                input.val(lines[0]);
              }, 0);
            }
          }
        }
      }
    },
    onkeyup(e) {
      if (e.keyCode == 90 && e.ctrlKey) {
        const target = $(e.target);
        const elementsChanged = target.data('pasteManipulatedElements');
        if (typeof elementsChanged !== 'undefined' && elementsChanged && elementsChanged.length) {
          for (let i = 0; i < elementsChanged.length; ++i) {
            elementsChanged[i].closest('tr').remove();
          }
          target.data('pasteManipulatedElements', []);
        }
      }
    },
    onaddnewinline(e) {
      if (e.target != this[0]) {
        return;
      }

      const tmpl = window.tmpl;
      const row = this.find('.ss-gridfield-add-inline-template:last');
      const num = this.data('add-inline-num') || 1;

      tmpl.cache[`${this[0].id}ss-gridfield-add-inline-template`] = tmpl(row.html());

      this.find('tbody:first').append(tmpl(`${this[0].id}ss-gridfield-add-inline-template`, { num }));
      this.find('tbody:first').children('.ss-gridfield-no-items').hide();
      this.data('add-inline-num', num + 1);

      // Rebuild sort order fields
      $('.ss-gridfield-orderable tbody').rebuildSort();
    }
  });

  $('.ss-gridfield-add-new-inline').entwine({
    onclick() {
      this.getGridField().trigger('addnewinline');
      return false;
    }
  });

  $('.ss-gridfield-delete-inline').entwine({
    onclick() {
      const msg = i18n._t('GridFieldExtensions.CONFIRMDEL', 'Are you sure you want to delete this?');

      if (confirm(msg)) {
        this.parents('tr.ss-gridfield-inline-new:first').remove();
      }

      return false;
    }
  });
});
