
$('.message-link').live('click', function () {
	var p = $(this).parents('div').eq(0)
	$('.message-short, .message-full', p).toggle();
	$('span', this).toggle();
	return false;
});

$('#structure .node.file').live('click', function (e) {
	if (e.button == 0 && e.shiftKey) {
		var editor = $(this).find('.actions .editor');
		location.href = editor.attr('href');
		e.preventDefault();
	}
});

$('.failure h3 > a, .error h3 > a').live('click', function (e) {
	if (e.button == 0 && e.shiftKey) {
		var editor = $(this).closest('h3').find('.editor a');
		location.href = editor.attr('href');
		e.preventDefault();
	}
});

$('#summary .details > a').live('click', function (e) {
	if (e.button == 0 && e.shiftKey) {
		var editor = $(this).closest('.details').find('.editor a');
		location.href = editor.attr('href');
		e.preventDefault();
	}
});
