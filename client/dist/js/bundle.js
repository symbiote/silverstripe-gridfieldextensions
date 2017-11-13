/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 10);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),
/* 1 */
/***/ (function(module, exports) {

module.exports = Injector;

/***/ }),
/* 2 */
/***/ (function(module, exports) {

module.exports = React;

/***/ }),
/* 3 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_react_dom__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_react_dom___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_react_dom__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_lib_Injector__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_lib_Injector___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_lib_Injector__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_components_GridFieldActionsMenu_GridFieldActionsMenuComponent__ = __webpack_require__(11);






var InjectedActionsMenu = __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_3_lib_Injector__["provideInjector"])(__WEBPACK_IMPORTED_MODULE_4_components_GridFieldActionsMenu_GridFieldActionsMenuComponent__["a" /* default */]);

__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.entwine('ss', function ($) {
  $('.js-injector-boot .ss-gridfield .actions-menu__activator').entwine({
    onmatch: function onmatch() {
      this.drawActionsMenu();

      $('.cms-edit-form')._setupChangeTracker();
    },
    getItemId: function getItemId() {
      return this.closest('.ss-gridfield-item').data('id');
    },
    getData: function getData() {
      return JSON.parse(decodeURIComponent(this.data('actions')) || '[]');
    },
    drawActionsMenu: function drawActionsMenu() {
      var items = [];
      this.getData().forEach(function (menuGroup) {
        if (items.length) {
          items.push(__WEBPACK_IMPORTED_MODULE_1_react___default.a.createElement('div', { className: 'dropdown-divider' }));
        }
        items.push(menuGroup.map(function (_ref) {
          var Title = _ref.Title,
              Link = _ref.Link,
              Type = _ref.Type;
          return __WEBPACK_IMPORTED_MODULE_1_react___default.a.createElement(
            'a',
            { href: Link, className: 'dropdown-item actions-menu__' + Type + '-action' },
            Title
          );
        }));
      });

      var scrollingContainer = this.closest('form').css('position', 'relative').get(0);
      __WEBPACK_IMPORTED_MODULE_2_react_dom___default.a.render(__WEBPACK_IMPORTED_MODULE_1_react___default.a.createElement(
        InjectedActionsMenu,
        { id: 'actions-menu_' + this.getItemId(), container: scrollingContainer },
        items
      ), this.get(0));
    }
  });
  $('.actions-menu__versioning-action').entwine({
    onclick: function onclick(e) {
      __WEBPACK_IMPORTED_MODULE_0_jquery___default.a.ajax({
        headers: { 'X-Pjax': 'CurrentForm,Breadcrumbs' },
        url: this.prop('href'),
        type: 'GET',
        complete: function complete() {},
        success: function success(data, status, xhr) {
          var newContentEls = $('.cms-container').handleAjaxResponse(data, status, xhr);
          if (!newContentEls) return;
          newContentEls.filter('form').trigger('aftersubmitform', { status: status, xhr: xhr });
        }
      });
      e.stopPropagation();
      return false;
    }
  });
});

/***/ }),
/* 4 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);


__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.entwine('ss', function ($) {
  $('.add-existing-search-dialog').entwine({
    loadDialog: function loadDialog(deferred) {
      var dialog = this.addClass('loading').children('.ui-dialog-content').empty();

      deferred.done(function (data) {
        dialog.html(data).parent().removeClass('loading');
      });
    }
  });

  $('.ss-gridfield .add-existing-search').entwine({
    onclick: function onclick() {
      var dialog = $('<div></div>').appendTo('body').dialog({
        modal: true,
        resizable: false,
        width: 500,
        height: 600,
        close: function close() {
          $(this).dialog('destroy').remove();
        }
      });

      dialog.parent().addClass('add-existing-search-dialog').loadDialog($.get(this.prop('href')));
      dialog.data('grid', this.closest('.ss-gridfield'));

      return false;
    }
  });

  $('.add-existing-search-dialog .add-existing-search-form').entwine({
    onsubmit: function onsubmit() {
      this.closest('.add-existing-search-dialog').loadDialog($.get(this.prop('action'), this.serialize()));
      return false;
    }
  });

  $('.add-existing-search-dialog .add-existing-search-items a').entwine({
    onclick: function onclick() {
      var link = this.closest('.add-existing-search-items').data('add-link');
      var id = this.data('id');

      var dialog = this.closest('.add-existing-search-dialog').addClass('loading').children('.ui-dialog-content').empty();

      $.post(link, { id: id }, function () {
        dialog.data('grid').reload();
        dialog.dialog('close');
      });

      return false;
    }
  });

  $('.add-existing-search-dialog .add-existing-search-pagination a').entwine({
    onclick: function onclick() {
      this.closest('.add-existing-search-dialog').loadDialog($.get(this.prop('href')));
      return false;
    }
  });
});

/***/ }),
/* 5 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_i18n__ = __webpack_require__(13);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_i18n___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_i18n__);





__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.entwine('ss', function ($) {
  $('.ss-gridfield.ss-gridfield-editable').entwine({
    reload: function reload(opts, success) {
      var grid = this;

      var added = [];
      var index = 0;
      grid.find('tbody:first .ss-gridfield-item').each(function () {
        if ($(this).is('.ss-gridfield-inline-new')) {
          added.push({
            index: index,
            row: $(this).detach()
          });
        }
        index++;
      });

      this._super(opts, function () {
        var body = grid.find('tbody:first');
        $.each(added, function (i, item) {
          var row = item.row,
              index = item.index,
              replaces = void 0;

          if (index === 0) {
            body.prepend(row);
          } else {
            replaces = body.find('.ss-gridfield-item:nth-child(' + index + ')');
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
    onpaste: function onpaste(e) {
      var clipboardData = typeof e.originalEvent.clipboardData !== 'undefined' ? e.originalEvent.clipboardData : null;
      if (clipboardData) {
        var input = $(e.target);
        var inputType = input.attr('type');
        if (inputType === 'text' || inputType === 'email') {
          var lastInput = this.find('.ss-gridfield-inline-new:last').find('input');
          if (input.attr('type') === 'text' && input.is(lastInput) && input.val() === '') {
            var inputWrapperDivClass = input.parent().attr('class');

            var lines = clipboardData.getData('text/plain').match(/[^\r\n]+/g);
            var linesLength = lines.length;

            if (linesLength > 1) {
              var elementsChanged = [];
              for (var i = 1; i < linesLength; ++i) {
                this.trigger('addnewinline');
                var row = this.find('.ss-gridfield-inline-new:last');
                var rowInput = row.find('.' + inputWrapperDivClass).find('input');
                rowInput.val(lines[i]);
                elementsChanged.push(rowInput);
              }

              input.data('pasteManipulatedElements', elementsChanged);

              setTimeout(function () {
                input.val(lines[0]);
              }, 0);
            }
          }
        }
      }
    },
    onkeyup: function onkeyup(e) {
      if (e.keyCode == 90 && e.ctrlKey) {
        var target = $(e.target);
        var elementsChanged = target.data('pasteManipulatedElements');
        if (typeof elementsChanged !== 'undefined' && elementsChanged && elementsChanged.length) {
          for (var i = 0; i < elementsChanged.length; ++i) {
            elementsChanged[i].closest('tr').remove();
          }
          target.data('pasteManipulatedElements', []);
        }
      }
    },
    onaddnewinline: function onaddnewinline(e) {
      if (e.target != this[0]) {
        return;
      }

      var tmpl = window.tmpl;
      var row = this.find('.ss-gridfield-add-inline-template:last');
      var num = this.data('add-inline-num') || 1;

      tmpl.cache[this[0].id + 'ss-gridfield-add-inline-template'] = tmpl(row.html());

      this.find('tbody:first').append(tmpl(this[0].id + 'ss-gridfield-add-inline-template', { num: num }));
      this.find('tbody:first').children('.ss-gridfield-no-items').hide();
      this.data('add-inline-num', num + 1);

      $('.ss-gridfield-orderable tbody').rebuildSort();
    }
  });

  $('.ss-gridfield-add-new-inline').entwine({
    onclick: function onclick() {
      this.getGridField().trigger('addnewinline');
      return false;
    }
  });

  $('.ss-gridfield-delete-inline').entwine({
    onclick: function onclick() {
      var msg = __WEBPACK_IMPORTED_MODULE_1_i18n___default.a._t('GridFieldExtensions.CONFIRMDEL', 'Are you sure you want to delete this?');

      if (confirm(msg)) {
        this.parents('tr.ss-gridfield-inline-new:first').remove();
      }

      return false;
    }
  });
});

/***/ }),
/* 6 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);


__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.entwine('ss', function ($) {
  $('.ss-gridfield-add-new-multi-class .btn__addnewmulticlass').entwine({
    onclick: function onclick() {
      var link = this.data('href');
      var cls = this.parents('.ss-gridfield-add-new-multi-class').find('select').val();

      if (cls && cls.length) {
        this.getGridField().showDetailView(link.replace('{class}', encodeURI(cls)));
      }

      return false;
    }
  });

  $('.ss-gridfield-add-new-multi-class select').entwine({
    onadd: function onadd() {
      this.update();
    },
    onchange: function onchange() {
      this.update();
    },
    update: function update() {
      var btn = this.parents('.ss-gridfield-add-new-multi-class').find('[data-add-multiclass]');

      if (this.val() && this.val().length) {
        btn.removeClass('disabled');
      } else {
        btn.addClass('disabled');
      }
    }
  });
});

/***/ }),
/* 7 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);


__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.entwine('ss', function ($) {
  $('.ss-gridfield-configurable-paginator .pagination-page-size-select').entwine({
    onchange: function onchange() {
      this.parent().find('.ss-gridfield-pagesize-submit').trigger('click');
    }
  });
});

/***/ }),
/* 8 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);


__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.entwine('ss', function ($) {
  $('.grid-field .ss-gridfield-item').entwine({
    onclick: function onclick(e) {
      if (this.find('.editable-column-field').length) {
        e.stopPropagation();
      }
    }
  });
});

/***/ }),
/* 9 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);


__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.entwine('ss', function ($) {
  $('.ss-gridfield-orderable tbody').entwine({
    rebuildSort: function rebuildSort() {
      var grid = this.getGridField();

      var minSort = null;
      grid.getItems().each(function () {
        var sortField = $(this).find('.ss-orderable-hidden-sort');
        if (sortField.length) {
          var thisSort = sortField.val();
          if (minSort === null && thisSort > 0) {
            minSort = thisSort;
          } else if (thisSort > 0) {
            minSort = Math.min(minSort, thisSort);
          }
        }
      });
      minSort = Math.max(1, minSort);

      var sort = minSort;
      grid.getItems().each(function () {
        var sortField = $(this).find('.ss-orderable-hidden-sort');
        if (sortField.length) {
          sortField.val(sort);
          sort++;
        }
      });
    },
    onadd: function onadd() {
      var self = this;

      var helper = function helper(e, row) {
        return row.clone().addClass('ss-gridfield-orderhelper').width('auto').find('.col-buttons').remove().end();
      };

      var update = function update(event, ui) {
        var postback = true;
        if (ui != undefined && ui.item.hasClass('ss-gridfield-inline-new')) {
          postback = false;
        }

        self.rebuildSort();

        var grid = self.getGridField();
        if (grid.data('immediate-update') && postback) {
          grid.reload({
            url: grid.data('url-reorder')
          });
        } else {
          var form = $('.cms-edit-form');
          form.addClass('changed');
        }
      };

      this.sortable({
        handle: '.handle',
        helper: helper,
        opacity: 0.7,
        update: update
      });
    },
    onremove: function onremove() {
      if (this.data('sortable')) {
        this.sortable('destroy');
      }
    }
  });

  $('.ss-gridfield-orderable .ss-gridfield-previouspage, .ss-gridfield-orderable .ss-gridfield-nextpage').entwine({
    onadd: function onadd() {
      var grid = this.getGridField();

      if (this.is(':disabled')) {
        return false;
      }

      var drop = function drop(e, ui) {
        var page = void 0;

        if ($(this).hasClass('ss-gridfield-previouspage')) {
          page = 'prev';
        } else {
          page = 'next';
        }

        grid.find('tbody').sortable('cancel');
        grid.reload({
          url: grid.data('url-movetopage'),
          data: [{ name: 'move[id]', value: ui.draggable.data('id') }, { name: 'move[page]', value: page }]
        });
      };

      this.droppable({
        accept: '.ss-gridfield-item',
        activeClass: 'ui-droppable-active ui-state-highlight',
        disabled: this.prop('disabled'),
        drop: drop,
        tolerance: 'pointer'
      });
    },
    onremove: function onremove() {
      if (this.hasClass('ui-droppable')) this.droppable('destroy');
    }
  });
});

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(4);
__webpack_require__(5);
__webpack_require__(6);
__webpack_require__(7);
__webpack_require__(8);
__webpack_require__(3);
__webpack_require__(9);

/***/ }),
/* 11 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* unused harmony export Component */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_lib_Injector__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_lib_Injector___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_lib_Injector__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_react__);



var ActionsMenu = function ActionsMenu(_ref) {
  var PopoverField = _ref.PopoverField,
      id = _ref.id,
      children = _ref.children,
      container = _ref.container;
  return __WEBPACK_IMPORTED_MODULE_1_react___default.a.createElement(
    PopoverField,
    {
      id: id,
      buttonSize: 'sm',
      data: { placement: 'bottom' },
      className: 'mr-0 btn-sm',
      popoverClassName: 'actions-menu__actions',
      container: container
    },
    children
  );
};

ActionsMenu.propTypes = {
  id: __WEBPACK_IMPORTED_MODULE_1_react__["PropTypes"].string.isRequired,
  PopoverField: __WEBPACK_IMPORTED_MODULE_1_react__["PropTypes"].oneOfType([__WEBPACK_IMPORTED_MODULE_1_react__["PropTypes"].node, __WEBPACK_IMPORTED_MODULE_1_react__["PropTypes"].func])
};

ActionsMenu.defaultProps = {
  id: '',
  PopoverField: null
};



/* harmony default export */ __webpack_exports__["a"] = (__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_0_lib_Injector__["inject"])(['PopoverField'], function (PopoverField) {
  return { PopoverField: PopoverField };
}, function () {
  return 'ActionsMenu';
})(ActionsMenu));

/***/ }),
/* 12 */
/***/ (function(module, exports) {

module.exports = ReactDom;

/***/ }),
/* 13 */
/***/ (function(module, exports) {

module.exports = i18n;

/***/ })
/******/ ]);
//# sourceMappingURL=bundle.js.map