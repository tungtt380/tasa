$(function(){
    $('table').tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
        0: { sorter: "text" },
        1: { sorter: "integer" },
        2: { sorter: "digit" },
        3: { sorter: "digit" },
        4: { sorter: "digit" },
        5: { sorter: "digit" },
        6: { sorter:false },
        7: { sorter:false },
        8: { sorter:false }
    }});

	$('span.pie1').sparkline('html',{type:'pie',offset:'+270',height:'3em',width:'3em',sliceColors:['#f58d8d','#558cdf']});
	$('span.pie2').sparkline('html',{type:'pie',offset:'+270',height:'3em',width:'3em',sliceColors:['#009c74','#cc0000']});
	$('span.pie3').sparkline('html',{type:'pie',offset:'+270',height:'3em',width:'3em',sliceColors:['#0068b7','#cc0000','#00cc00']});
});
