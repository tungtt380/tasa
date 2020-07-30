$(function($){
	// TODO: IE6 Problem
	outsourcing = $('input[name=outsourcing]:checked').val();
	if (outsourcing != 1) {
		$('#tabcontact').attr('style','display:none');
	} else {
		$('#tabcontact').attr('style','display:block');
	}

	$('#outsourcing0').click(function(){
		$('#tabcontact').hide('bilnd', '', 400);
	});
	$('#outsourcing1').click(function(){
		$('#tabcontact').show('bilnd', '', 400);
	});

	$('#zip').keyup(function(){
		zip_buf = $(this).val();
		if (zip_buf.length == 7 && $('#address1').val().length == 0) {
			deferred = $.ajax({
				type: 'GET',
				url: '/v1/zipsearch',
				data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
				dataType: 'jsonp',
				timeout: 1000,
				cache: true
			});
			deferred.success(function(data){
	  			if(data.zipcode.a1 != undefined) {
					$('#prefecture').val(data.zipcode.a1.prefecture);
					$('#address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
				} else if(data.office.o1 != undefined) {
					$('#prefecture').val(data.office.o1.prefecture);
					$('#address1').val(data.office.o1.city + data.office.o1.town + data.office.o1.street);
				}
			});
		}
	});

	$('#zipdir').click(function(){
		zip_buf = $('#zip').val();
		deferred = $.ajax({
			type: 'GET',
			url: '/v1/zipsearch',
			data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#prefecture').val(data.zipcode.a1.prefecture);
				$('#address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
			} else if(data.office.o1 != undefined) {
				$('#prefecture').val(data.office.o1.prefecture);
				$('#address1').val(data.office.o1.city + data.office.o1.town + data.office.o1.street);
			}
		});
	});

	$('#ziprev').click(function(){
		pref = $('#prefecture').val();
		addr = $('#address1').val();
		deferred = $.ajax({
			type: 'GET',
			url: '/v1/zipsearch',
			data: 'word=' + pref + addr + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});;
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#zip').val(data.zipcode.a1.zipcode);
			} else if(data.office.o1 != undefined) {
				$('#zip').val(data.office.o1.zipcode);
			}
		});
	});
});
