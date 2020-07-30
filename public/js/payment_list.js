$(function(){
	$('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		0: { sorter: "text" },
		1: { sorter: "text" },
		2: { sorter: "text" },
		3: { sorter: "text" },
		4: { sorter: "text" },
		6: { sorter: "currency" },
		7: { sorter: "currency" },
		8: { sorter: "currency" },
		9: { sorter: "text" }
   	}});
	$('#q').attr('speech','speech');
	$('#q').attr('x-webkit-speech','x-webkit-speech');
	$('#q').attr('placeholder','検索キーを入力');

	if($.fn.datepicker){
        $.datepicker.setDefaults($.extend($.datepicker.regional['ja']));
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: (new Date(2012, 7-1, 18, 10, 0, 0)),
            maxDate: +10
        });
	}
});
