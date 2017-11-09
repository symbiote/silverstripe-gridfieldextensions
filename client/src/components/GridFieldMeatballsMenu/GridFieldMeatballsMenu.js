import jQuery from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import { provideInjector } from 'lib/Injector';
import Meatballs from 'components/GridFieldMeatballsMenu/GridFieldMeatballsComponent';

const InjectedMeatballs = provideInjector(Meatballs);

jQuery.entwine('ss', $ => {
  $('.js-injector-boot .ss-gridfield .meatball-menu__activator').entwine({
    onmatch() {
      this.drawMeatballs();
      // reinstantiate the changetracker otherwise we will get false positives
      // due to the react component not existing in the DOM at the time of
      // page load, and `undefined !== ""``
      this.closest('form').changetracker();
    },
    getItemId() {
      return this.closest('.ss-gridfield-item').data('id');
    },
    getData() {
      return JSON.parse(decodeURIComponent(this.data('actions')) || '[]');
    },
    drawMeatballs() {
      const items = [];
      this.getData().forEach((menuGroup) => {
        if (items.length) {
          items.push(<div className="dropdown-divider" />);
        }
        items.push(
          menuGroup.map(({ Title, Link, Type }) => (
            <a href={Link} className={`dropdown-item meatball-menu__${Type}-action`}>
              {Title}
            </a>
          )),
        );
      });
      // The CMS <body> doesn't scroll as the Popover component expects,
      // so we need to position relative to the thing that does, to keep it aligned.
      // (and also assure it is positioned appropriately to support this)
      const scrollingContainer = this.closest('form')
        .css('position', 'relative')
        .get(0);
      ReactDOM.render(
        <InjectedMeatballs id={`meatball-menu_${this.getItemId()}`} container={scrollingContainer}>
          {items}
        </InjectedMeatballs>,
        this.get(0),
      );
    },
  });
  $('.meatball-menu__versioning-action').entwine({
    onclick(e) {
      jQuery.ajax({
        headers: { 'X-Pjax': 'CurrentForm,Breadcrumbs' },
        url: this.prop('href'),
        type: 'GET',
        complete() {
          // TODO in future when we move to "toast" style pop-up feedback,
          // Remove a row's loading state feedback here.
        },
        success(data, status, xhr) {
          const newContentEls = $('.cms-container').handleAjaxResponse(data, status, xhr);
          if (!newContentEls) return;
          newContentEls.filter('form').trigger('aftersubmitform', { status, xhr });
        },
      });
      e.stopPropagation();
      return false;
    },
  });
});
