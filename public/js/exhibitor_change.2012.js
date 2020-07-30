$(function($){
/*
	$('form').exValidation({
		rules: {
			corpname: "required",
			corpkana: "required furigana",
			fullname: "required",
			fullkana: "required furigana",
			brandname: "required",
			brandkana: "required furigana",
		},
		errFocus: true,
		errTipCloseBtn: false,
	});
*/
	//$('#tabs').tabs();
	var $tab = $('#tabs').tabs(); // first tab selected

	$('.next-tab').click(function() { // bind click event to link
		var selected = $tab.tabs('option', 'selected'); // => 0
    	$tab.tabs('select',selected+1); // switch to third tab
    	return false;
	});

    if($.fn.datetimepicker){
        $.datepicker.setDefaults($.extend($.datepicker.regional['ja']));
        $('.datepicker').datetimepicker({
            dateFormat: 'yy-mm-dd',
            timeFormat: 'hh:mm:ss',
			showMinute: true,
			showSecond: true,
			minDate: (new Date(2012, 7-1, 18, 10, 0, 0)),
			maxDate: +30
        });
    }
	/*
	$('.zipsearch').css("cursor","pointer").hover(
		function(){this.style.color = "blue";},
		function(){this.style.color = "black";}
	);
	$('.revsearch').css("cursor","pointer").hover(
		function(){this.style.color = "blue";},
		function(){this.style.color = "black";}
	);
	*/
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
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
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
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
		});
	});

	$('#m_zip').keyup(function(){
		zip_buf = $(this).val();
		if (zip_buf.length == 7 && $('#m_address1').val().length == 0) {
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
					$('#m_prefecture').val(data.zipcode.a1.prefecture);
					$('#m_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
				} else if(data.office.o1 != undefined) {
					$('#m_prefecture').val(data.office.o1.prefecture);
					$('#m_address1').val(data.office.o1.city + data.office.o1.town + data.office.o1.street);
				}
			});
		}
	});

	$('#m_zipdir').click(function(){
		zip_buf = $('#m_zip').val();
		deferred = $.ajax({
			type: 'GET',
			url: '/v1/zipsearch',
			data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#m_prefecture').val(data.zipcode.a1.prefecture);
				$('#m_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
			} else if(data.office.o1 != undefined) {
				$('#m_prefecture').val(data.office.o1.prefecture);
				$('#m_address1').val(data.office.o1.city + data.office.o1.town + data.office.o1.street);
			}
		});
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
		});
	});

	$('#m_ziprev').click(function(){
		pref = $('#m_prefecture').val();
		addr = $('#m_address1').val();
		deferred = $.ajax({
			type: 'GET',
			url: '/v1/zipsearch',
			data: 'word=' + pref + addr + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#m_zip').val(data.zipcode.a1.zipcode);
			} else if(data.office.o1 != undefined) {
				$('#m_zip').val(data.office.o1.zipcode);
			}
		});
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
		});
	});

	$('#b_zip').keyup(function(){
		zip_buf = $(this).val();
		if (zip_buf.length == 7 && $('#b_address1').val().length == 0) {
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
					$('#b_prefecture').val(data.zipcode.a1.prefecture);
					$('#b_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
				} else if(data.office.o1 != undefined) {
					$('#b_prefecture').val(data.office.o1.prefecture);
					$('#b_address1').val(data.office.o1.city + data.office.o1.town + data.office.o1.street);
				}
			});
		}
	});
	$('#b_zipdir').click(function(){
		zip_buf = $('#b_zip').val();
		deferred = $.ajax({
			type: 'GET',
			url: '/v1/zipsearch',
			data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#b_prefecture').val(data.zipcode.a1.prefecture);
				$('#b_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
			} else if(data.office.o1 != undefined) {
				$('#b_prefecture').val(data.office.o1.prefecture);
				$('#b_address1').val(data.office.o1.city + data.office.o1.town + data.office.o1.street);
			}
		});
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
		});
	})

	$('#b_ziprev').click(function(){
		pref = $('#b_prefecture').val();
		addr = $('#b_address1').val();
		deferred = $.ajax({
			type: 'GET',
			url: '/v1/zipsearch',
			data: 'word=' + pref + addr + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#b_zip').val(data.zipcode.a1.zipcode);
			} else if(data.office.o1 != undefined) {
				$('#b_zip').val(data.office.o1.zipcode);
			}
		});
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
		});
	});

	$('#c_zip').keyup(function(){
		zip_buf = $(this).val();
		if (zip_buf.length == 7 && $('#c_address1').val().length == 0) {
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
					$('#c_prefecture').val(data.zipcode.a1.prefecture);
					$('#c_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
				} else if(data.office.o1 != undefined) {
					$('#c_prefecture').val(data.office.o1.prefecture);
					$('#c_address1').val(data.office.o1.city + data.office.o1.town + data.office.o1.street);
				}
			});
		}
	});
	$('#c_zipdir').click(function(){
		zip_buf = $('#c_zip').val();
		deferred = $.ajax({
			type: 'GET',
			url: '/v1/zipsearch',
			data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#c_prefecture').val(data.zipcode.a1.prefecture);
				$('#c_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
			} else if(data.office.o1 != undefined) {
				$('#c_prefecture').val(data.office.o1.prefecture);
				$('#c_address1').val(data.office.o1.city + data.office.o1.town + data.office.o1.street);
			}
		});
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
		});
	})

	$('#c_ziprev').click(function(){
		pref = $('#c_prefecture').val();
		addr = $('#c_address1').val();
		deferred = $.ajax({
			type: 'GET',
			url: '/v1/zipsearch',
			data: 'word=' + pref + addr + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#c_zip').val(data.zipcode.a1.zipcode);
			} else if(data.office.o1 != undefined) {
				$('#c_zip').val(data.office.o1.zipcode);
			}
		});
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
		});
	});

	$('#d_zip').keyup(function(){
		zip_buf = $(this).val();
		if (zip_buf.length == 7 && $('#d_address1').val().length == 0) {
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
					$('#d_prefecture').val(data.zipcode.a1.prefecture);
					$('#d_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
				} else if(data.office.o1 != undefined) {
					$('#d_prefecture').val(data.office.o1.prefecture);
					$('#d_address1').val(data.office.o1.city + data.office.o1.town + data.office.o1.street);
				}
			});
		}
	});
	$('#d_zipdir').click(function(){
		zip_buf = $('#d_zip').val();
		deferred = $.ajax({
			type: 'GET',
			url: '/v1/zipsearch',
			data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#d_prefecture').val(data.zipcode.a1.prefecture);
				$('#d_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
			} else if(data.office.o1 != undefined) {
				$('#d_prefecture').val(data.office.o1.prefecture);
				$('#d_address1').val(data.office.o1.city + data.office.o1.town + data.office.o1.street);
			}
		});
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
		});
	})

	$('#d_ziprev').click(function(){
		pref = $('#d_prefecture').val();
		addr = $('#d_address1').val();
		deferred = $.ajax({
			type: 'GET',
			url: '/v1/zipsearch',
			data: 'word=' + pref + addr + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#d_zip').val(data.zipcode.a1.zipcode);
			} else if(data.office.o1 != undefined) {
				$('#d_zip').val(data.office.o1.zipcode);
			}
		});
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
		});
	});
	$('.btn').click(function(){
		res = $(this).attr('name');
		if (res != "testundefined") {
			resar = res.split('_');
		}
		switch(resar[1]){
		case 'corp':
			src = ''; break;
		case 'manage':
			src = 'm_'; break;
		case 'bill':
			src = 'b_'; break;
		case 'contact':
			src = 'c_'; break;
		case 'dist':
			src = 'd_'; break;
		}
		switch(resar[0]){
		case 'corp':
			dst = ''; break;
		case 'manage':
			dst = 'm_'; break;
		case 'bill':
			dst = 'b_'; break;
		case 'contact':
			dst = 'c_'; break;
		case 'dist':
			dst = 'd_'; break;
		}
		$('#' + dst + 'corpname').val($('#' + src + 'corpname').val());
		$('#' + dst + 'corpkana').val($('#' + src + 'corpkana').val());
		$('#' + dst + 'zip').val($('#' + src + 'zip').val());
		$('#' + dst + 'prefecture').val($('#' + src + 'prefecture').val());
		$('#' + dst + 'address1').val($('#' + src + 'address1').val());
		$('#' + dst + 'address2').val($('#' + src + 'address2').val());
		$('#' + dst + 'section').val($('#' + src + 'section').val());
		$('#' + dst + 'division').val($('#' + src + 'division').val());
		$('#' + dst + 'position').val($('#' + src + 'position').val());
		$('#' + dst + 'fullname').val($('#' + src + 'fullname').val());
		$('#' + dst + 'fullkana').val($('#' + src + 'fullkana').val());
		$('#' + dst + 'phone').val($('#' + src + 'phone').val());
		$('#' + dst + 'fax').val($('#' + src + 'fax').val());
		$('#' + dst + 'mobile').val($('#' + src + 'mobile').val());
		$('#' + dst + 'email').val($('#' + src + 'email').val());
	});
});
