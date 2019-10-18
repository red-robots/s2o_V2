/**
 *	Custom jQuery Scripts
 *	
 *	Developed by: Austin Crane	
 *	Designed by: Austin Crane
 */

jQuery(document).ready(function ($) {
	
	/*
	*
	*	Flexslider
	*
	------------------------------------*/
	$('.flexslider').flexslider({
		animation: "slide",
	}); // end register flexslider
	
	/*
	*
	*	Colorbox
	*
	------------------------------------*/
	$('a.gallery').colorbox({
		rel:'gal',
		width: '80%', 
		height: '80%'
	});


	$("#primary-menu > li > a").each(function(){
		var wave = $("#waveSvg").html();
		$(this).append(wave);
	});
	

	/*
	*
	*	Wow Animation
	*
	------------------------------------*/
	new WOW().init();


	$(document).on("click",".menu-toggle",function(){
		$(this).toggleClass('open');
		$('body').toggleClass('open-menu');
		$(".main-navigation").toggleClass('open');
		$(".main-menu").slideToggle();
	});

	var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
	var is_scroll = false;
	var ii = 1;

	if( $(".animateOnScroll").length ) {
		$(window).on('scroll', function() {
	      var scrollTop = $(window).scrollTop();
	      var line_height = $(".animateOnScroll .line").outerHeight();
	      var barHeight = $(".animateOnScroll").outerHeight();
	      var currentPage = $(window).height();

	      $(".paddle").addClass('action');

	      if(scrollTop>200) {
	      	$(".animateOnScroll .scrollObj").addClass("fixed");
	      	//$(".animateOnScroll .scrollObj").css("margin-top","20px");
	      } else {
	      	$(".animateOnScroll .scrollObj").removeClass("fixed");
	      	//$(".animateOnScroll .scrollObj").css("margin-top","");
	      }

			if($(window).scrollTop() + $(window).height() == $(document).height()) {
				$(".animateOnScroll .scrollObj").addClass('bottom');
			} else {
				$(".animateOnScroll .scrollObj").removeClass('bottom');
			}

	      ii++;
	   });


		if($(window).scrollTop() + $(window).height() == $(document).height()) {
			$(".animateOnScroll .scrollObj").addClass('bottom');
		} else {
			$(".animateOnScroll .scrollObj").removeClass('bottom');
		}


		$.fn.scrollStopped = function(callback) {
		  var that = this, $this = $(that);
		  $this.scroll(function(ev) {
		    clearTimeout($this.data('scrollTimeout'));
		    $this.data('scrollTimeout', setTimeout(callback.bind(that), 250, ev));
		  });
		};

		$(window).scrollStopped(function(ev){
		  $(".paddle").removeClass('action');
		});


	    var dashes = $(".animateOnScroll .line").html();
	    var scrollHtml = "<div class='animateOnScroll continue'><div class='line'>"+dashes+"</div></div>";
	    $("body.home .section").not(".row1").each(function(){
	    	$(this).append(scrollHtml);
	    });

	}


});// END #####################################    END