$(function(){
	$('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		0: { sorter: "text" },
		1: { sorter: "text" }
   	}});
});
