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
				var id = Number($el.attr('data-addon-id'));
				addons[id] = $el.attr('data-addon-name');
				$table.append($el.clone().attr('id', 'addon-' + id));
			}
		});

		$("input.addons-search").keyup(function(e) {
			var $el = $(e.target);
			var queryString = $el.val();
			if (queryString.length < 1) {
				$list.show();
				$result.hide();
				return;
			} else {
				$list.hide();
				$result.show();
			}
			var show = addons.slice(0);
			var hide = [];
			var parts = queryString.split(' ');
			for (var i in parts) {
				var partRegexp = new RegExp(parts[i].replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1"), 'i');
				for (var id in show) {
					if (!partRegexp.test(show[id])) {
						hide[id] = show[id];
						delete show[id];
					}
				}
			}
			for (var id in show) {
				$('#addon-'+id).show();
			}
			for (var id in hide) {
				$('#addon-'+id).hide();
			}
		});

		$('#categories-list a').click(function () {
			$("input.addons-search").val('').keyup();
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
