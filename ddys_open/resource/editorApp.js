/*
 * PHPWind WindEditor DDYS shortcode plugin
 */
;(function ($, window) {
	var appName = 'ddys_open';
	var options = [
		['ddys_latest', '[ddys_latest limit="12"]'],
		['ddys_hot', '[ddys_hot limit="10"]'],
		['ddys_search', '[ddys_search]'],
		['ddys_calendar', '[ddys_calendar]'],
		['ddys_movie', '[ddys_movie slug="this-tempting-madness"]'],
		['ddys_sources', '[ddys_sources slug="this-tempting-madness"]'],
		['ddys_collections', '[ddys_collections page="1"]'],
		['ddys_request_form', '[ddys_request_form]']
	];

	function insertText(editor, text) {
		if (editor && typeof editor.insertHTML === 'function') {
			editor.insertHTML(text);
			return;
		}
		var textarea = editor && editor.textarea ? editor.textarea : $('textarea.wind_editor_textarea');
		if (!textarea || !textarea.length) return;
		var node = textarea[0];
		var start = node.selectionStart || node.value.length;
		var end = node.selectionEnd || node.value.length;
		node.value = node.value.slice(0, start) + text + node.value.slice(end);
		node.focus();
	}

	WindEditor.initOpenApp[appName](function (item, rootPath) {
		var editor = this;
		var base = rootPath + appName + '/';
		var icon = $('<div class="wind_icon" data-control="' + appName + '"><span class="' + appName + '" title="低端影视" style="background:url(' + base + 'images/icon.png) no-repeat center center;background-size:16px 16px;"></span></div>').appendTo(editor.pluginsContainer);
		var dialog = $('<div class="edit_menu ddys-editor-menu" style="display:none;position:absolute;z-index:9999;"><ul></ul></div>');
		$.each(options, function (_, pair) {
			$('<li><a href="javascript:;">' + pair[0] + '</a></li>').appendTo(dialog.find('ul')).on('click', function () {
				insertText(editor, pair[1]);
				dialog.hide();
			});
		});
		icon.on('click', function () {
			if ($(this).hasClass('disabled')) return;
			if (!$.contains(document.body, dialog[0])) dialog.appendTo(document.body);
			var offset = icon.offset();
			dialog.css({ left: offset.left, top: offset.top + icon.outerHeight() + 4 }).show();
		});
		$(document).on('click.ddys_open_editor', function (event) {
			if (!$(event.target).closest(icon).length && !$(event.target).closest(dialog).length) dialog.hide();
		});
	});
})(jQuery, window);
