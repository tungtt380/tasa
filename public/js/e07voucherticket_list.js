$(function(){
	Shadowbox.init();
    $('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
        0: { sorter: "numeric" },
        1: { sorter: "text" }
    }});
});
