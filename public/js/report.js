$(function(){
	$('table').tablesorter({widthFixed: true, widgets: ['zebra']});

	$('span.pie1').sparkline('html',{type:'pie',height:'3em',width:'3em',sliceColors:['#0068b7','#ffcc00']});
	$('span.pie2').sparkline('html',{type:'pie',height:'3em',width:'3em',sliceColors:['#009c74','#cc0000']});
	$('span.pie3').sparkline('html',{type:'pie',height:'3em',width:'3em',sliceColors:['#0068b7','#cc0000']});
});
