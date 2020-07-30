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
	var availableTags = [
"出展料",
"イベントステージ使用料",
"イベントホール使用料",
"コピー代",
"フーズ協力金",
"公式ガイドブック",
"車両搬送費",
"一次側幹線工事費",
"取材協力費",
"企画協力費",
"電気使用料",
"有料控室料",
"特別招待券",
"一般招待券",
"広告料",
"保険料",
"事務経費として",
"雑費",
	];
	$("input.ac").autocomplete({
		source: availableTags
	});
});
