$(function(){
	$('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
		0: { sorter: "digit" },
		1: { sorter: "text" },
		2: { sorter: "text" },
		3: { sorter: "text" },
		4: { sorter: "text" },
		5: { sorter: "text" }
   	}});
});
