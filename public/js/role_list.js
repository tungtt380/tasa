$(function(){
	$('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		0: { sorter: "integer" },
		1: { sorter: "text" },
		2: { sorter: "text" }
   	}});
	$('#q').attr('speech','speech');
	$('#q').attr('x-webkit-speech','x-webkit-speech');
	$('#q').attr('placeholder','検索キーを入力');
});
