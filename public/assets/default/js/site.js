 var windowWidth = $(window).width();
 var dashMenuExpanded = 0;
 
 $(document).ready(function() {
	// fix menu when passed
	$(".masthead").visibility({
	  once: false,
	  onBottomPassed: function() {
		$(".fixed.menu").transition("fade in");
	  },
	  onBottomPassedReverse: function() {
		$(".fixed.menu").transition("fade out");
	  }
	});

	$('.ui.menu')
    .on('click', '.item', function() {
      if(!$(this).hasClass('dropdown')) {
        $(this)
          .addClass('active')
          .siblings('.item')
            .removeClass('active');
      }
    });
	// create sidebar and attach to menu open
	if($('.toc.item').length > 0){
		$(".ui.sidebar").sidebar("attach events", ".toc.item");
	}
    //$('.ui.dropdown').dropdown();
	$('.ui.dropdown').dropdown({ on: 'hover' });
	$('.nav_item_' + current_route ).addClass('active');
	
	$('.lang_selector')
	  .dropdown({
		onChange: function(value, text, $selectedItem) {
			var u = $selectedItem.attr('data-href');
			$('#change-loc').attr('action', u).submit();
		}
	  });
	  $('.main-sidebar').sidebar({
		context: $('.main-sidebar-segment')
	  });
	  //.sidebar('attach events', '.menu .item');
	
	$('.billing_period_dropdown')
	  .dropdown({
		onChange: function(value, text, $selectedItem) {
			elem = $(this).closest('.generic_content').find('.price_selected');
			val = JSON.parse(elem.attr('data-config'));
			dvalue = val[value];
			elem.find('.currency').html(dvalue[0]);
			elem.find('.cent').html(dvalue[1]);
			elem.find('.month').html(dvalue[2]);
			$('input[name="billing_interval"]').val(value);
		}
	  });
	  
	$('.payment_cycle_dropdown')
	  .dropdown({
		onChange: function(value, text, $selectedItem) {
			$('input[name="payment_cycle"]').val(value);
		}
	  });	
	
	$('.main-sidebar').hover(
		function(){
			//if($(this).hasClass('collapse-main-sidebar'))return;
			if($('.main-sidebar').find('span:visible').length == 0 ){
				if(!dashMenuExpanded){
					if(windowWidth > 768)ml = '0px';
					else ml = '160px';
					$('.main-sidebar').animate({width: '230px'}, 'fast', function(){
						$('.main-sidebar').find('span').show();
					});
					$('.main-sidebar-segment').find('.pusher').css( { marginLeft : ml } );
					dashMenuExpanded = 1;
				}
			}
		}, 
		function(){
			if($(this).hasClass('collapse-main-sidebar'))return;
			if(dashMenuExpanded){				
				if($('.main-sidebar').find('span:visible').length > 0 ){
					if(windowWidth > 768)ml = '-160px';
					else ml = '0px';
					$('.main-sidebar').find('span').hide();
					$('.main-sidebar').animate({width: '70px'});
					$('.main-sidebar-segment').find('.pusher').css( { marginLeft : ml } );
					dashMenuExpanded = 0;
				}
			}
		}
	);	
	
	$('.collapse-main-sidebar').click(function(){
		parent_elem = $('.main-sidebar');
		if(parent_elem.find('span:visible').length > 0 && !dashMenuExpanded ){
			sidebar_width = '70px';
			if(windowWidth > 768)ml = '-160px';
			else if(windowWidth <= 500){ml = '0px';sidebar_width = '0px';}
			else ml = '0px';
			parent_elem.find('span').hide();
			$('.main-sidebar').animate({width: sidebar_width});
			$('.main-sidebar-segment').find('.pusher').css( { marginLeft : ml } );
			$(this).find('span').html(js_locale.expand);
		}
		else{
			if(windowWidth > 768)ml = '0px';
			else if(windowWidth <= 500)ml = '240px';
			else ml = '160px';
			$('.main-sidebar-segment').find('.pusher').css( { marginLeft : ml } );
			$('.main-sidebar').animate({width: '230px'}, 'fast', function(){
				$('.main-sidebar').find('span').show();
			});
			$(this).find('span').html(js_locale.collapse);
			dashMenuExpanded = 0;
		}
		return false;
	});

	$('.main-sidebar-segment').find('.pusher').click(function(){
		if(windowWidth <= 768){
			ml = '0px';
			
			if(windowWidth <= 500)sidebar_width = '0px';
			else sidebar_width = '70px';
			
			$('.main-sidebar').find('span').hide();
			$('.main-sidebar').animate({width: sidebar_width});
			$('.main-sidebar-segment').find('.pusher').css( { marginLeft : ml } );
			$('.collapse-main-sidebar').find('span').html(js_locale.expand);
		}
	});
	
	$('#fbSignInBtn').click(function(){
		fbLoginAttempt();
	});
	
	$('.continue_with_payment').click(function(e){
		e.preventDefault();
		elem = $('input[name="payment_gateway"]:checked');
		if(elem.length <= 0)return fail(js_locale.choose_payment_method);
		var url = elem.attr('data-url');
		$('.payment_summary_table_dimmer').show();
		$('.payment_method_submit_form').attr('action', url).submit();
	});
	
	$('.continue_with_membership').click(function(e){
		e.preventDefault();
		$('.payment_summary_table_dimmer').show();
		$('.payment_method_submit_form').attr('action', payment_zero_url).submit();
	});
	
	$('.payment_method_selector').click(function(){
		$(this).parent('p').find('input[name="payment_gateway"]').prop("checked", true);;
	});
  });
  
  $(window).resize(function(){
	  windowWidth = $(window).width();
	  /*
	  if($('.login_grid').length > 0){
		  if(windowWidth > 700 && windowWidth < 990){
			  $('.login_grid').removeClass('three').removeClass('column').addClass('two').addClass('column');
		  }
		  else $('.login_grid').removeClass('two').removeClass('column').addClass('three').addClass('column');
	  }
	  */
  });
  
  window.fbAsyncInit = function() {
    FB.init({
      appId      : FB_API_KEY,
      cookie     : true,                     // Enable cookies to allow the server to access the session.
      xfbml      : true,                     // Parse social plugins on this webpage.
	  version	 : 'v7.0'
	});
  }
  
  function fbLoginAttempt()
  {
	  FB.login(function(response) {
		$(document).find('.message').remove();
		  console.log(response);
		if (response.status === 'connected') {
			var id_token = response.authResponse['accessToken'];
			$(document).find('.message').remove();
			$('.rWrap').parent().addClass('loading');
			$.post(social_login_url, {
				'id_token': id_token,
				'site': 'facebook'
			}, function(r){
				if(r.error != ''){
						$('.rWrap').parent().removeClass('loading');
						$('.login_header').after(
							'<div class="ui negative message">'+
								'<div class="header">'+js_locale.error+'</div>'+
								'<p>'+ r.error +'</p>'+
							'</div>'
						);
				}
				else{
					window.location.href = user_dashboard_url;
				}
			});
		} 
		else {
			$('.login_header').after(
				'<div class="ui negative message">'+
					'<div class="header">'+js_locale.error+'</div>'+
					'<p>'+ response.status +'</p>'+
				'</div>'
			);
		}
	  }, {scope: 'public_profile,email'});
  }
  
  function gooLoginonLoad()
  {
	  gapi.load('auth2', function() {
		auth2 = gapi.auth2.init({
		  client_id: GOO_API_KEY,
		  cookiepolicy: 'single_host_origin',
		  scope: 'profile'
		});

	  auth2.attachClickHandler(document.getElementById('gooSignInBtn'), {},
		function(googleUser) {
			$(document).find('.message').remove();
			var id_token = googleUser.getAuthResponse().id_token;
			$('.rWrap').parent().addClass('loading');
			
			$.post(social_login_url, {
				'id_token': id_token,
				'site': 'google'
			}, function(r){
				if(r.error != ''){
						$('.rWrap').parent().removeClass('loading');
						$('.login_header').after(
							'<div class="ui negative message">'+
								'<div class="header">'+js_locale.error+'</div>'+
								'<p>'+ r.error +'</p>'+
							'</div>'
						);
				}
				else{
					window.location.href = user_dashboard_url;
				}
			});
			
		  }, function(error) {
			$(document).find('.message').remove();
			console.log('Sign-in error', error);
			$('.login_header').after(
				'<div class="ui negative message">'+
					'<div class="header">'+js_locale.error+'</div>'+
					'<p>'+ error.error +'</p>'+
				'</div>'
			);
		  }
		);
	  });
  }
  
  $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	
  function success(msg)
  {
	$.uiAlert({
		textHead: js_locale.success, // header
		text: msg, // Text
		bgcolor: '#19c3aa', // background-color
		textcolor: '#fff', // color
		position: 'top-right',// position . top And bottom ||  left / center / right
		icon: 'checkmark box', // icon in semantic-UI
		time: 5, // time
	})
  }  
  
  function fail(msg)
  {
	$.uiAlert({
		textHead: js_locale.error, // header
		text: msg, // Text
		bgcolor: '#DB2828', // background-color
		textcolor: '#fff', // color
		position: 'bottom-right',// position . top And bottom ||  left / center / right
		icon: 'remove circle', // icon in semantic-UI
		time: 5, // time
	})
  }
  
	


/* alerts */
  
$.uiAlert = function(options) {
  var setUI = $.extend({
    textHead: 'Your user registration was successful.',
    text: 'You may now log-in with the username you have chosen',
    textcolor: '#19c3aa',
    bgcolors: '#fff',
    position: 'top-right',
    icon: '',
    time: 5,
    permanent: false
  }, options);

    var ui_alert = 'ui-alert-content';
      ui_alert += '-' + setUI.position;
      setUI.bgcolors ='style="background-color: '+setUI.bgcolor+';   box-shadow: 0 0 0 1px rgba(255,255,255,.5) inset,0 0 0 0 transparent;opacity: 0; border-radius:15px"';
      if(setUI.bgcolors === '') setUI.bgcolors ='style="background-color: ; box-shadow: 0 0 0 1px rgba(255,255,255,.5) inset,0 0 0 0 transparent;"';
    if(!$('body > .' + ui_alert).length) {
      $('body').append('<div class="ui-alert-content ' + ui_alert + '" style="width: inherit;"></div>');
    }
    var message = $('<div id="messages" class="ui icon message" ' + setUI.bgcolors + '><i class="'+setUI.icon+' icon" style="color: '+setUI.textcolor+';"></i><i class="close icon" style="color: '+setUI.textcolor+';" id="messageclose"></i><div style="color: '+setUI.textcolor+'; margin-right: 10px;">   <div class="header">'+setUI.textHead+'</div>  <p> '+setUI.text+'</p></div>  </div>');
    $('.' + ui_alert).prepend(message);
    message.animate({
      opacity: '0.95',
    }, 400);
    if(setUI.permanent === false){
      var timer = 0;
      $(message).mouseenter(function(){
        clearTimeout(timer);
      }).mouseleave(function(){
        uiAlertHide();
      });
      uiAlertHide();
    }
    function uiAlertHide(){
      timer = setTimeout(function() {
        message.animate({
          opacity: '0',
        }, 400, function() {
          message.remove();
        });
      }, (setUI.time * 1000) );
    }

    $('#messageclose')
    .on('click', function() {
      $(this)
        .closest('#messages')
        .transition('fade')
      ;
    });
  };

