$(function(){
    $('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		0: { sorter: "integer" },
		1: { sorter: "integer" },
		2: { sorter: "text" },
		3: { sorter: "text" },
		4: { sorter: "text" },
		5: { sorter: "integer" }
    }});
	$('#q').attr('speech','speech');
	$('#q').attr('x-webkit-speech','x-webkit-speech');
	$('#q').attr('placeholder','検索キーを入力');
});
