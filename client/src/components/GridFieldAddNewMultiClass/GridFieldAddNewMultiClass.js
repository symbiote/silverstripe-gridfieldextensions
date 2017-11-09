import jQuery from 'jquery';

jQuery.entwine('ss', ($) => {
  /**
   * GridFieldAddNewMultiClass
   */
  $('.ss-gridfield-add-new-multi-class .btn__addnewmulticlass').entwine({
    onclick() {
      const link = this.data('href');
      const cls = this.parents('.ss-gridfield-add-new-multi-class').find('select').val();

      if (cls && cls.length) {
        this.getGridField().showDetailView(link.replace('{class}', encodeURI(cls)));
      }

      return false;
    }
  });

  $('.ss-gridfield-add-new-multi-class select').entwine({
    onadd() {
      this.update();
    },
    onchange() {
      this.update();
    },
    update() {
      const btn = this.parents('.ss-gridfield-add-new-multi-class').find('[data-add-multiclass]');

      if (this.val() && this.val().length) {
        btn.removeClass('disabled');
      } else {
        btn.addClass('disabled');
      }
    }
  });
});
