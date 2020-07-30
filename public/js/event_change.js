$(function($){
	if($.fn.datepicker){
		$.datepicker.setDefaults($.extend($.datepicker.regional['ja']));
		$('.datepicker').datepicker({
			dateFormat: 'yy-mm-dd'
		});
	}
});