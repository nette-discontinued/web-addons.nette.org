$(document).ready(function() {
	var $main = $('#main');
	var $loading = $('<div class="spinner-container"><h1>Working...</h1><div class="spinner"></div></div>').css({
		height: $main.height() + 'px',
		width: $main.width() + 'px'
	});

	$('[data-addons-busy]').click(function (e) {
		if (e.ctrlKey) return;

		$main.append($loading);
		$(window).on('keyup.addons', function (e) {
			if (e.keyCode == 27) {
				$loading.detach();
				$(window).off('keyup.addons');
			}
		});
	});

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

	$("#content h2[id|=toc], #content h3[id|=toc]").each(function () {
		$(this).append($('<a class="anchor">#</a>').attr("href", "#" + $(this).attr("id")));
	});

	$(".chzn-select").chosen();


	if ($("input.addons-search").length > 0) {
		var addons = [];

		var $table = $('<table class="table-full"></table>');
		var $result = $('<div class="addons-search-result"></div>');
		$result.append($('<h2>Results</h2>')).append($table).hide();
		var $list = $('.addons-categorized-list');
		$list.after($result);

		$("tr.addon").each(function(i, el) {
			if (typeof addons[el.id] == "undefined") {
				var $el = $(el);
				var id = $el.attr('data-addon-id')
				addons['addon-'+id] = $el.attr('data-addon-name');
				$table.append($el.clone().attr('id', 'addon-'+id));
			}
		});

		$("input.addons-search").keyup(function(e) {
			$el = $(e.target);
			queryRegex = '';
			queryString = $el.val();
			if (queryString.length < 1) {
				$list.show();
				$result.hide();
				return;
			} else {
				$list.hide();
				$result.show();
			}
			parts = queryString.split(' ');
			for (var i=0;i<parts.length;i++) {
				if (queryRegex.length > 0) {
					queryRegex += '(.*)';
				}
				queryRegex += parts[i].replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
			}
			regexp = new RegExp(queryRegex, 'i');
			for (var id in addons) {
				if (regexp.test(addons[id])) {
					$('#'+id).show();
				} else {
					$('#'+id).hide();
				}
			};
		});
	}



	var $downloadsGraph = $('#downloads-graph');
	if ($downloadsGraph.length) {
		google.load('visualization', '1.0', { 'packages' : ['corechart'], 'callback' : function () {
			var input = [
				['Den', 'Downloads + Installs']
			];
			$.each($downloadsGraph.data('netteaddonsDownloads'), function () {
				input.push([this.date, this.count]);
			});
			var data = google.visualization.arrayToDataTable(input);

			var options = {
				height : 60,
				backgroundColor : { fill : 'transparent' },
				fontSize : 10,
				chartArea : {
					width : '100%',
					height : '80%'
				},
				hAxis : { textPosition : 'none' },
				vAxis : {
					format : '#',
					baselineColor: '#ddd',
					textPosition : 'in',
					textStyle : { fontSize: 8 },
					gridlines : {
						color : 'transparent',
						count : 2
					}
				},
				legend : { position : 'none' },
				series : { 0 : { color : '#26374e' } }
			};

			var chart = new google.visualization.LineChart($downloadsGraph.get(0));
			chart.draw(data, options);
		} });
	}
});
