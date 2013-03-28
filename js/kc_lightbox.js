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
		
		//$("#lightbox_list").html("");
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
		
		lightbox.css({
			"top" : 20 ,
			"left": (win_width/2) - (lightbox.width()/2)
		}).addClass("modalwindow_active").fadeIn(fadespeed).show();
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
			var doc_width = $(document).width();
			var doc_height = $(document).height();
	
			var win_width = $(window).width();
			var win_height = $(window).height();
	
			var mask_width = (doc_width > win_width) ? doc_width : win_width ;
			var mask_height = (doc_height > win_height) ? doc_height : win_height ;
	
			$("#modalwindow_mask").css({
				"width" : mask_width,
				"height": mask_height
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


