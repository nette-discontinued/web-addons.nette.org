$(document).ready(function() {
	$('[data-addons-toggle]').each(function(i, el) {
		$(el).click(function(e) {
			e.preventDefault();

			$this = $(this);
			$($this.attr('rel')).slideToggle('default', function () {
				$(this).find('.chzn-select').chosen();
				// $('#content').appendTo($('#main')); // hack
			});

			hRel = $this.attr('data-addons-toggle');
			if (hRel) {
				$(hRel).slideToggle();
			}
		});
	});

	$.nette.init();

	//$(".chzn-select").chosen();
});
