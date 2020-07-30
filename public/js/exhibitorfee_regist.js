$(function(){
	$('input.numeric').bind("keyup", function(){
		var item = $(this);
		var id = item.attr('id');
		var val = item.attr('value');
		var idlabel = id.slice(0,2);
		var idno = id.slice(2);
		$('div#xA'+idno).html('&yen;' + number_format(eval($('input#xQ'+idno).val()) * eval($('input#xP'+idno).val())));
	});
	function number_format(num){
		return num.toString().replace(/([0-9]+?)(?=(?:[0-9]{3})+$)/g, '$1,');
	}
});
