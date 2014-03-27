$(document).ready(function() {
	var $sidebar = $('#sidebar');
	var $content = $('#content');
	var $loading = $('<div class="spinner-container"><h1>Working&hellip;</h1></div>');
	var spinnerOpts = {
		lines: 15, length: 30, width: 10, radius: 30, corners: 1, rotate: 0, color: '#000', speed: 1,
		trail: 30, shadow: true, hwaccel: true, className: 'spinner', zIndex: 2e9, top: 'auto', left: 'auto'
	};
	var spinner = new Spinner(spinnerOpts).spin();
	$loading.append(spinner.el);

	function busy(e) {
		if (e.ctrlKey) return;

		$loading.insertAfter($sidebar);
		$content.hide();
		$(window).on('keyup.addons', function(e) {
			if (e.keyCode == 27) {
				$content.show();
				$loading.detach();
				$(window).off('keyup.addons');
			}
		});
	}

	$('a[data-addons-busy]').click(busy);
	$('form[data-addons-busy]').submit(busy);

	$('[data-addons-toggle]').each(function(i, el) {
		$(el).click(function(e) {
			e.preventDefault();

			$this = $(this);
			$($this.attr('rel')).slideToggle('default');

			hRel = $this.attr('data-addons-toggle');
			if (hRel) {
				$(hRel).slideToggle();
			}
		});
	});

	$.nette.init();

	$('#content h2[id|=toc], #content h3[id|=toc]').each(function() {
		$(this).append($('<a class="anchor">#</a>').attr('href', '#' + $(this).attr('id')));
	});

	$('.chzn-select').chosen();


	var $searchInput = $('input.addons-search');
	if ($searchInput.length > 0) {
		var $table = $('<table class="table-full"></table>');
		var $result = $('<div class="addons-search-result"></div>');
		$result.append($('<h2>Results</h2>')).append($table).hide();
		var $list = $('.addons-categorized-list');
		$list.after($result);

		var addons = [];
		var usedIds = [];
		$list.find('tr.addon').sort(function(a, b) {
			return $(b).data('addonScore') - $(a).data('addonScore');

		}).each(function(i, el) {
			var $el = $(el);
			var id = parseInt($el.data('addonId'), 10);
			if ($.inArray(id, usedIds) == -1) {
				var text = $el.find('.name').text() + ' ' + $el.find('.description').text();
				var row =  $el.clone();
				$table.append(row);
				usedIds.push(id);
				addons.push({
					'$el': row,
					'text': $.trim(text).toLowerCase()
				});
			}
		});

		$searchInput.on('search keyup', function(e) {
			var query = $.trim(e.target.value).toLowerCase();
			if (query.length === 0) {
				$list.show();
				$result.hide();

			} else {
				$list.hide();
				$result.show();

				var words = query.split(/\s+/);
				var odd = true;
				$.each(addons, function(idx, addon) {
					for (var i = 0; i < words.length; i++) {
						if (addon.text.indexOf(words[i]) === -1) {
							addon.$el.hide();
							return;
						}
					}
					addon.$el.toggleClass('alt', odd).show();
					odd = !odd;
				});
			}
		});

		$('#categories-list').find('a').click(function() {
			$searchInput.val('').keyup();
		});
	}
});
