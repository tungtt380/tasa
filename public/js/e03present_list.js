$(function(){
	$('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		3: { sorter: "digit" }
   	}});
	$('#q').attr('speech','speech');
	$('#q').attr('x-webkit-speech','x-webkit-speech');
	$('#q').attr('placeholder','検索キーを入力');
});
