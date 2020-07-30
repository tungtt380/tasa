$(function(){
	$('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		0: { sorter: "digit" },
		1: { sorter: "text" },
		2: { sorter: "text" },
		3: { sorter: "integer" },
		4: { sorter: "text" },
		5: { sorter: "text" },
		6: { sorter: "integer" },
		6: { sorter: "text" }
   	}});
	$('#q').attr('speech','speech');
	$('#q').attr('x-webkit-speech','x-webkit-speech');
	$('#q').attr('placeholder','検索キーを入力');
});
