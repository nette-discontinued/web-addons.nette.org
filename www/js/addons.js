require(['jquery'], ['netteForms'], function($) {
	$(document).ready(function() {
		$('[data-addons-toggle]').each(function(i, el) {
			$(el).click(function(e) {
				e.preventDefault();

				$this = $(this);
				$($this.attr('rel')).slideToggle();
				hRel = $this.attr('data-addons-toggle');
				if (hRel) {
					$(hRel).slideToggle();
				}
			});
		});
	});
});