(function($) {
	$.entwine("ss", function($) {
		/**
		 * GridFieldAddExistingSearchButton
		 */

		$(".add-existing-search-dialog").entwine({
			loadDialog: function(deferred) {
				var dialog = this.addClass("loading").children(".ui-dialog-content").empty();

				deferred.done(function(data) {
					dialog.html(data).parent().removeClass("loading");
				});
			}
		});

		$(".ss-gridfield .add-existing-search").entwine({
			onclick: function() {
				var dialog = $("<div></div>").appendTo("body").dialog({
					modal: true,
					resizable: false,
					width: 500,
					height: 600,
					close: function() {
						$(this).dialog("destroy").remove();
					}
				});

				dialog.parent().addClass("add-existing-search-dialog").loadDialog(
					$.get(this.prop("href"))
				);
				dialog.data("grid", this.closest(".ss-gridfield"));

				return false;
			}
		});

		$(".add-existing-search-dialog .add-existing-search-form").entwine({
			onsubmit: function() {
				this.closest(".add-existing-search-dialog").loadDialog($.get(
					this.prop("action"), this.serialize()
				));
				return false;
			}
		});

		$(".add-existing-search-dialog .add-existing-search-items a").entwine({
			onclick: function() {
				var link = this.closest(".add-existing-search-items").data("add-link");
				var id   = this.data("id");

				var dialog = this.closest(".add-existing-search-dialog")
				                 .addClass("loading")
				                 .children(".ui-dialog-content")
				                 .empty()

				$.post(link, { id: id }, function() {
					dialog.data("grid").reload();
					dialog.dialog("close");
				});

				return false;
			}
		});

		$(".add-existing-search-dialog .add-existing-search-pagination a").entwine({
			onclick: function() {
				this.closest(".add-existing-search-dialog").loadDialog($.get(
					this.prop("href")
				));
				return false;
			}
		});

		/**
		 * GridFieldAddNewMultiClass
		 */

		$(".ss-gridfield-add-new-multi-class .ss-ui-button").entwine({
			onclick: function() {
				var link = this.prop("href");
				var cls  = this.parents(".ss-gridfield-add-new-multi-class").find("select").val();

				if(cls && cls.length) {
					this.getGridField().showDetailView(link + "/" + cls);
				}

				return false;
			}
		});

		$(".ss-gridfield-add-new-multi-class select").entwine({
			onadd: function() {
				this.update();
			},
			onchange: function() {
				this.update();
			},
			update: function() {
				var btn = this.parents(".ss-gridfield-add-new-multi-class").find(".ss-ui-button");

				if(this.val() && this.val().length) {
					btn.button("enable");
				} else {
					btn.button("disable");
				}
			}
		});

		/**
		 * GridFieldEditableColumns
		 */

		$('.ss-gridfield.ss-gridfield-editable .ss-gridfield-item').entwine({
			onclick: function() {
				// Stop the default click action when fields are clicked on.
			}
		});

		/**
		 * GridFieldOrderableRows
		 */

		$(".ss-gridfield-orderable tbody").entwine({
			onadd: function() {
				var self = this;

				var helper = function(e, row) {
					return row.clone()
					          .addClass("ss-gridfield-orderhelper")
					          .width("auto")
					          .find(".col-buttons")
					          .remove()
					          .end();
				};

				var update = function() {
					var grid = self.getGridField();

					var data = grid.getItems().map(function() {
						return { name: "order[]", value: $(this).data("id") };
					});

					grid.reload({
						url: grid.data("url") + "/reorder",
						data: data.get()
					});
				};

				this.sortable({
					handle: ".handle",
					helper: helper,
					opacity: .7,
					update: update
				});
			},
			onremove: function() {
				this.sortable("destroy");
			}
		});

		$(".ss-gridfield-orderable .ss-gridfield-previouspage, .ss-gridfield-orderable .ss-gridfield-nextpage").entwine({
			onadd: function() {
				var grid = this.getGridField();

				if(this.is(":disabled")) {
					return false;
				}

				var drop = function(e, ui) {
					var page;

					if($(this).hasClass("ss-gridfield-previouspage")) {
						page = "prev";
					} else {
						page = "next";
					}

					grid.find("tbody").sortable("cancel");
					grid.reload({
						url: grid.data("url") + "/movetopage",
						data: [
							{ name: "move[id]", value: ui.draggable.data("id") },
							{ name: "move[page]", value: page }
						]
					});
				};

				this.droppable({
					accept: ".ss-gridfield-item",
					activeClass: "ui-droppable-active ui-state-highlight",
					disabled: this.prop("disabled"),
					drop: drop,
					tolerance: "pointer"
				});
			},
			onremove: function() {
				if(this.hasClass("ui-droppable")) this.droppable("destroy");
			}
		});
	});
})(jQuery);
