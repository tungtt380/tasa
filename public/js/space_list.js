$(function(){
    $('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		0: { sorter: "integer" },
		1: { sorter: "text" },
		2: { sorter: "currency" },
		3: { sorter: "currency" },
		4: { sorter: "integer" },
		5: { sorter: "currency" },
		6: { sorter: "integer" },
		7: { sorter: "text" },
		8: { sorter: "integer"}
    }});
	$('#q').attr('speech','speech');
	$('#q').attr('x-webkit-speech','x-webkit-speech');
	$('#q').attr('placeholder','検索キーを入力');
});
