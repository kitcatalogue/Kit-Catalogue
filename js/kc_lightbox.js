/*
 * Lightbox Script
 * 
 */



$.require([
	"ecl.js" ,
	"jquery.dump.js" ,
]);



$(document).ready( function() {

	
	function showLightbox(image, image_list) {
	
		var lightbox = $('#lightbox');
	
		if (0 == $('#modalwindow_mask').length) {
			$("body").prepend("<div id=\"modalwindow_mask\"></div>");
		}
		
		if (0 == $("#lightbox_list").html().length) {
			$.each(image_list, function(index, value) {
				var img = $('<img width="80" height="80" />').attr("src", value);
				$("#lightbox_list").append(img);
			});
		}
		showLightboxImage(image);
	
		
		var fadespeed = 400;
	
		var doc_width = $(document).width();
		var doc_height = $(document).height();
	
		var win_width = $(window).width();
		var win_height = $(window).height();
	
		
		var mask_width = (doc_width > win_width) ? doc_width : win_width ;
		var mask_height = (doc_height > win_height) ? doc_height : win_height ;
	
		$("#modalwindow_mask").css({
			"width" : mask_width,
			"height": mask_height
		}).fadeTo(fadespeed, 0.80).show();

		
		var lightbox_width = (win_width > 604) ? 604 : win_width - 20 ;
		var lightbox_height = (win_height > 704) ? 704 : win_height - 40 ;
		
		lightbox.css({
			"height": lightbox_height ,
			"width" : lightbox_width ,
			"top"   : 20 ,
			"left"  : (win_width/2) - (lightbox.width()/2) ,
		}).addClass("modalwindow_active").fadeIn(fadespeed).show();
		
		
		var imgcontainer_width = lightbox_width;
		var imgcontainer_height = lightbox_height - 140;

		$('#lightbox_imagecontainer').css({
			"height": imgcontainer_height ,
			"width" : imgcontainer_width ,
		});
		
		$('#lightbox_image').css({
			"max-height": imgcontainer_height - 4 ,
			"max-width" : imgcontainer_width - 4 ,
		});
		
		$('#lightbox_list').css({
			"max-width" : imgcontainer_width + 4 ,
		});
	
	}
	
	
	
	function closeLightbox() {
		var fadespeed = 200;
		$(".modalwindow_active").removeClass("modalwindow_active").hide();
	    $("#modalwindow_mask").fadeOut(fadespeed);
	}
	
	
	
	function showLightboxImage(image) {
		$('#lightbox_list img').each( function() {
			if (image == $(this).attr("src")) {
				$(this).addClass("active");
			} else {
				$(this).removeClass("active");
			}
		});
		$('#lightbox_link').attr('href', image);
		$('#lightbox_image').attr('src', image);
	}
	
	
	
	$(window).resize(function () {
		var obj = $(".modalwindow_active");
	
		if (obj.length > 0) {
			
			var fadespeed = 400;
			
			var doc_width = $(document).width();
			var doc_height = $(document).height();
	
			var win_width = $(window).width();
			var win_height = $(window).height();
	
			
			var mask_width = (doc_width > win_width) ? doc_width : win_width ;
			var mask_height = (doc_height > win_height) ? doc_height : win_height ;
				
			$("#modalwindow_mask").css({
				"width" : mask_width ,
				"height": mask_height ,
			});
	
			
			var lightbox = $('#lightbox');
	
			var lightbox_width = (win_width > 604) ? 604 : win_width - 20 ;
			var lightbox_height = (win_height > 704) ? 704 : win_height - 20 ;
			
			lightbox.css({
				"height": lightbox_height ,
				"width" : lightbox_width ,
				"top"   : 20 ,
				"left"  : (win_width/2) - (lightbox_width/2) ,
			});
			
			
			var imgcontainer_width = lightbox_width;
			var imgcontainer_height = lightbox_height - 140;

			$('#lightbox_imagecontainer').css({
				"height": imgcontainer_height ,
				"width" : imgcontainer_width ,
			});
			
			$('#lightbox_image').css({
				"max-height": imgcontainer_height - 4 ,
				"max-width" : imgcontainer_width - 4 ,
			});
			
			$('#lightbox_list').css({
				"max-width" : imgcontainer_width + 4 ,
			});
			
			
			obj.css({
				"top" : 20 ,
				"left": (win_width/2) - (obj.width()/2)
			});
		}
	});
	
	
	
	$(document).on("click", ".modalwindow_close", function (e) {
	    e.preventDefault();
		closeLightbox();
	});
	

	
	// Force any youtube iframes to go below the modal mask
	$('iframe').each(function() {
		$(this).attr("src", $(this).attr("src") + "?wmode=transparent");
	});

	
	$(".images a").on("click", function(e) {
		e.preventDefault();
	});

	
	var image_list = $(".images img").map( function() {
		return $(this).attr("src");
	}).get();
	
	$(document).on("click", ".images img, #lightbox_list img", function(e) {
		showLightbox($(this).attr("src"), image_list);
	});
	
	
});


