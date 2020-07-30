$(function(){
//var availableTags = [
//"有料控室料",
//];
//	$("input#q").autocomplete({source:availableTags});
	$("input#q").autocomplete({source:'/op/itemfee/autocomplete'});
});
