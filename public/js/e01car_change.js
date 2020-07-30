$(function($){

	spec = $('input[name=spec]:checked').val();
	$('#tabdressup').attr('style','display:none');
	$('#tabconcept').attr('style','display:none');
	$('#tabtuning').attr('style','display:block');

	$('#spec0').click(function(){
		$('#tabdressup').attr('style','display:none');
		$('#tabconcept').attr('style','display:none');
		$('#tabtuning').show('bilnd', '', 400);
	});
	$('#spec1').click(function(){
		$('#tabtuning').attr('style','display:none');
		$('#tabconcept').attr('style','display:none');
		$('#tabdressup').show('bilnd', '', 400);
	});
	$('#spec2').click(function(){
		$('#tabtuning').attr('style','display:none');
		$('#tabdressup').attr('style','display:none');
		$('#tabconcept').show('bilnd', '', 400);
	});

	if ($.fn.datetimepicker) {
		$.datepicker.setDefaults($.extend($.datepicker.regional['ja']));
		$('.datepicker').datetimepicker({
			dateFormat: 'yy-mm-dd',
			timeFormat: 'hh:mm',
			stepMinute: 10,
			ampm: false,
			minDate: (new Date(2019, 11-1,  5)),
			maxDate: (new Date(2020,  1-1, 13))
		});
	} else if($.fn.datepicker){
		$.datepicker.setDefaults($.extend($.datepicker.regional['ja']));
		$('.datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
			minDate: (new Date(2019, 11-1,  5)),
			maxDate: (new Date(2020,  1-1, 13))
		});
	}
});
