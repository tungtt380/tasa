$(function(){
	$('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		2: { sorter: "currency" },
		3: { sorter: "currency" },
		4: { sorter: "currency" }
   	}});
	$('#q').attr('speech','speech');
	$('#q').attr('x-webkit-speech','x-webkit-speech');
	$('#q').attr('placeholder','検索キーを入力');
});
