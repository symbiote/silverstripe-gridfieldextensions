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
	});
})(jQuery);
