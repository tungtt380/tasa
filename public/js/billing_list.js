$(function(){
	$('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		0: { sorter: "text" },
		1: { sorter: "text" }
   	}});
	$('#q').attr('speech','speech');
	$('#q').attr('x-webkit-speech','x-webkit-speech');
	$('#q').attr('placeholder','検索キーを入力');

	$('input:checkbox').click(function(){
		var n = $('input:checkbox:checked').length;
		if (n > 0) {
			$("#merge").removeAttr('disabled');
		} else {
			$("#merge").attr('disabled','disabled');
		}
	});
});
