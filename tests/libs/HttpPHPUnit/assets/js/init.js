$.extend($.fn.disableTextSelect = function () {
	return this.each(function () {
		if ($.browser.mozilla) {
			$(this).css('MozUserSelect', 'none');
		} else if($.browser.msie) {
			$(this).bind('selectstart', function () {
				return false;
			});
		} else{
			$(this).mousedown(function () {
				return false;
			});
		}
	});
});

var Progress = (function () {
	var number = 0, numberAll = 1;
	var infoCount, infoText, progressBar, title;

	var interval;
	if (window.chrome && window.chrome.csi)
	{
		interval = setInterval(function () {
			if (window.chrome.csi().onloadT !== 0) Progress.ready();
		}, 1000);
	}
	$(function () {
		Progress.ready();
	});

	return {
		start: function (countAll) {
			numberAll = countAll;
			$('header h2 .info .countAll').text(countAll);
			$('header h2 .info').show();
			infoCount = $('header h2 .info .count');
			infoText = $('header h2 .info .text');
			progressBar = $('header h2 .progressBar');
			title = $('title');
		},
		add: function (text) {
			infoCount.text(number);
			infoText.text(text);
			progressBar.css('width', (number/numberAll)*100 + '%');
			title.text('Tests | ' + number + '/' + numberAll + ' | ' + text);
			number++
		},
		end: function () {
			Progress.add('');
		},
		ready: function () {
			clearInterval(interval);
			var header = $('header');
			var sentence = $('#sentence');
			var state = sentence.data('state') || 'unknown';
			var text = sentence.text() || 'ERROR';
			header.addClass(state);
			header.find('h2').text(text);
			if (!title) title = $('title');
			title.text('Tests | ' + state + ' | ' + text);

			// aktualizace favicon
			var favicon = $('head > link[rel=icon]');
			favicon.attr('href', favicon.attr('href').replace(/(\.ico)$/, '-' + state + '$1'));
			favicon.remove().appendTo('head'); // kvůli FF
		}
	};
})();
