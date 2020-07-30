$(function(){
	$('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		0: { sorter: "text" },
		1: { sorter: "text" },
		2: { sorter: "text" },
		3: { sorter: "text" }
   	}});
});
