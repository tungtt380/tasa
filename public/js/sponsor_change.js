$(function($){
    var $tab = $('#tabs').tabs(); // first tab selected
    $('.next-tab').click(function() { // bind click event to link
        var selected = $tab.tabs('option', 'selected'); // => 0
        $tab.tabs('select',selected+1); // switch to third tab
        return false;
    });
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
				url: 'http://groovetechnology.co.jp/ZipSearchService/v1/zipsearch',
				data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
				dataType: 'jsonp',
				timeout: 1000,
				cache: true
			});
			deferred.success(function(data){
	  			if(data.zipcode.a1 != undefined) {
					$('#prefecture').val(data.zipcode.a1.prefecture);
					$('#address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
				}
			});
			deferred.error(function(data){
				alert('サービス混雑のためデータを取得できません。');
			});
		}
	});

	$('#zipdir').click(function(){
		zip_buf = $('#zip').val();
		deferred = $.ajax({
			type: 'GET',
			url: 'http://groovetechnology.co.jp/ZipSearchService/v1/zipsearch',
			data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#prefecture').val(data.zipcode.a1.prefecture);
				$('#address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
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
			url: 'http://groovetechnology.co.jp/ZipSearchService/v1/zipsearch',
			data: 'word=' + pref + addr + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#zip').val(data.zipcode.a1.zipcode);
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
				url: 'http://groovetechnology.co.jp/ZipSearchService/v1/zipsearch',
				data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
				dataType: 'jsonp',
				timeout: 1000,
				cache: true
			});
			deferred.success(function(data){
	  			if(data.zipcode.a1 != undefined) {
					$('#b_prefecture').val(data.zipcode.a1.prefecture);
					$('#b_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
				}
			});
			deferred.error(function(data){
				alert('サービス混雑のためデータを取得できません。');
			});
		}
	});
	$('#b_zipdir').click(function(){
		zip_buf = $('#b_zip').val();
		deferred = $.ajax({
			type: 'GET',
			url: 'http://groovetechnology.co.jp/ZipSearchService/v1/zipsearch',
			data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#b_prefecture').val(data.zipcode.a1.prefecture);
				$('#b_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
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
			url: 'http://groovetechnology.co.jp/ZipSearchService/v1/zipsearch',
			data: 'word=' + pref + addr + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#b_zip').val(data.zipcode.a1.zipcode);
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
				url: 'http://groovetechnology.co.jp/ZipSearchService/v1/zipsearch',
				data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
				dataType: 'jsonp',
				timeout: 1000,
				cache: true
			});
			deferred.success(function(data){
				if(data.zipcode.a1 != undefined) {
					$('#c_prefecture').val(data.zipcode.a1.prefecture);
					$('#c_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
				}
			});
			deferred.error(function(data){
				alert('サービス混雑のためデータを取得できません。');
			});
		}
	});
	$('#c_zipdir').click(function(){
		zip_buf = $('#c_zip').val();
		deferred = $.ajax({
			type: 'GET',
			url: 'http://groovetechnology.co.jp/ZipSearchService/v1/zipsearch',
			data: 'zipcode=' + zip_buf + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#c_prefecture').val(data.zipcode.a1.prefecture);
				$('#c_address1').val(data.zipcode.a1.city + data.zipcode.a1.town);
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
			url: 'http://groovetechnology.co.jp/ZipSearchService/v1/zipsearch',
			data: 'word=' + pref + addr + '&format=json&ie=UTF-8',
			dataType: 'jsonp',
			timeout: 1000
		});
		deferred.success(function(data){
			if(data.zipcode.a1 != undefined) {
				$('#c_zip').val(data.zipcode.a1.zipcode);
			}
		});
		deferred.error(function(data){
			alert('サービス混雑のためデータを取得できません。');
		});
	});

	$('.btn').click(function(){
		res = $(this).attr('name');
		resar = res.split('_');
		switch(resar[1]){
		case 'corp':
			src = ''; break;
		case 'bill':
			src = 'b_'; break;
		case 'contact':
			src = 'c_'; break;
		}
		switch(resar[0]){
		case 'corp':
			dst = ''; break;
		case 'bill':
			dst = 'b_'; break;
		case 'contact':
			dst = 'c_'; break;
		}
		$('#' + dst + 'corpname').val($('#' + src + 'corpname').val());
		$('#' + dst + 'corpkana').val($('#' + src + 'corpkana').val());
		$('#' + dst + 'zip').val($('#' + src + 'zip').val());
		$('#' + dst + 'prefecture').val($('#' + src + 'prefecture').val());
		$('#' + dst + 'address1').val($('#' + src + 'address1').val());
		$('#' + dst + 'address2').val($('#' + src + 'address2').val());
		$('#' + dst + 'section').val($('#' + src + 'section').val());
		$('#' + dst + 'position').val($('#' + src + 'position').val());
		$('#' + dst + 'fullname').val($('#' + src + 'fullname').val());
		$('#' + dst + 'fullkana').val($('#' + src + 'fullkana').val());
		$('#' + dst + 'phone').val($('#' + src + 'phone').val());
		$('#' + dst + 'fax').val($('#' + src + 'fax').val());
		$('#' + dst + 'mobile').val($('#' + src + 'mobile').val());
	});
});
