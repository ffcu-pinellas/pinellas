$(document).ready(function(){

	$("#g-recaptcha-response").attr('aria-hidden', 'true');

// for pageinfo
	var W = $( window ).width();
	var H = $( window ).height();
	$("#p1").text(W+' x '+H);

	// on resizing
	$( window ).resize(function() {
		var W = $( window ).width();
		var H = $( window ).height();
		$("#p1").text(W+' x '+H);
	});


// tabbing fields
	$("input").keyup(function () {
		if (this.value.length == this.maxLength) {
		  $(this).next('input').focus();
		}
	});

// alert
	$("#exed").click(function(){
		$(".alertbar").slideUp("fast");
	});


// search 
    $(".sitesearch").css('display', 'none');

	$('#sitesearch').click(function(){
        //$(".sitesearch").slideToggle("slow");
        $(".sitesearch").animate({width: 'toggle'}, 'slow');
	});
	$('.searchex').click(function(){
        $(".sitesearch").slideUp("fast");
	});

// hb
	$('#hbbttn').click(function(){
		var Hhb = $('.hb_bg').height();
		var W = $( window ).width();
		if(Hhb < 90)
		{
			if(W > 600)
				$('.hb_bg').css('height', '220px');
		}
		else
		{
			if(W > 800)
				$('.hb_bg').css('height', '60px');
			else
				$('.hb_bg').css('height', '66px');
			$(this).removeClass('rotated');
		}
	});



// on resizing
/*	$( window ).resize(function() {
	});*/

// FRC menu
	$( '#FRCmenu' ).click(function() {
		$(".FRCmenu").toggleClass("out");
		$( "#FRCmenu span").toggleClass("out");
	});

// mobi menu

	$( 'ul.mobimenu button' ).click(function() {
		var which = $(this).attr("which");
		$("#list"+which).slideToggle("slow");
		$(this).toggleClass("flipped");
	});


// stickiness

	$(window).scroll(function(){
		var Htop = $('.alertbar').height();
		var wintop = $(window).scrollTop();
		var W = $( window ).width();
		var headH = $('header').height();
		
		if(wintop > Htop && W > 1200)
		{
			$('header').addClass('stuck');
			$('#contentwrapper').css('padding-top', headH+'px');
		}
		else
		{
			$('header').removeClass('stuck');
			$('#contentwrapper').css('padding-top', '0');
		}

	});



// menu
	$( 'html' ).click(function() {
		var how = $('.CMitem').length;
		var many = $('.CM').length;
		var howmany = how + many;
		for(x = 1; x <= howmany; x++)
			$("#CMc"+x).fadeOut("fast");
		$('.opened').removeClass( "opened" );
		$(".CMsub").fadeOut('fast');
	});


	$( '.CMitem' ).click(function(event) {
		var which = $(this).attr("rel");
		event.stopPropagation();
		if($( this ).hasClass( "opened" ) )
		{
			$(this).removeClass( "opened" );
			$("#CMc"+which).fadeOut("fast");
		}
		else
		{
			var how = $('.CMitem').length;
			var many = $('.CM').length;
			var howmany = how + many;
			for(x = 1; x <= howmany; x++)
			{
				$("#CMc"+x).fadeOut("fast");
				$('.opened').removeClass( "opened" );
			}
			$("#CMc"+which).fadeIn("fast");
			$( this ).toggleClass( "opened" );
		}
	});

	$( '.CMcontent' ).click(function(event) {
		event.stopPropagation();
	});


/* for 3rd level 
	$( '.CMlink' ).click(function(event) {
		if($( this ).hasClass( "opened" ) )
		{
		}
		else
		{
			event.stopPropagation();
			var which = $(this).attr("rel");
			$(".CMsub").fadeOut('fast');
			$("#"+which).animate({width: 'toggle'}, 'slow');
			$( '.CMlink' ).removeClass( "opened" );
			$( this ).toggleClass( "opened" );
		}
	});*/



});
