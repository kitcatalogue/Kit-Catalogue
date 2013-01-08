/**
 * Modal Window plugin for JQuery.
 *
 * @author  Paul Newman
 * @version  1.0.0
 * 
 * 
 */



(function($) {


	var modalwindow = null;


	$.fn.showModal = function (open_callback, close_callback) {

		modalwindow = this;

		var height = $(window).height();
		var width = $(window).width();

		if ($('#modalwindow_mask').length === 0) {
			$(document).ready( function() {

			 	$('<div id="modalwindow_mask"></div>').css({
			 		'display': 'none' ,
			 		'position': 'fixed' ,
			 		'z-index': '9990' ,
			 		'top': 0 ,
			 		'left': 0 ,
			 		'height': $(document).height() ,
			 		'width':  $(window).width() ,
			 		'background-color': '#000' ,
			 	}).bind('mousedown.modalwindow', function(e){
		            return false;
		        }).appendTo('body');

			});
		}

		this.css({
			'position' : 'fixed' ,
			'z-index'  : 9999 ,
			'top'      : (height/2) - (this.height()/2) ,
			'left'     : (width/2) - (this.width()/2) ,
		});

		this.find('.js_modal_close').click( function() {
			modalwindow.closeModal();
		}).css('cursor', 'pointer');


		$(window).on('resize.modalwindow', function() {

			var height = $(window).height();
			var width = $(window).width();

		 	$('#js_modal_mask').css({
		 		'height' : $(document).height() ,
		 		'width'  : width ,
		 	});

			if (this && (0 < this.length)) {
				this.css({
					'top'  : (height/2) - (this.height()/2) ,
					'left' : (width/2) - (this.width()/2) ,
				});
			}

		});


		// Process callbacks, if defined
		if (open_callback) { open_callback(this); }
		if (close_callback) { this.on('modalwindow_close.modalwindow', function (e) {
			close_callback(this);
		}); }


		$('#modalwindow_mask').fadeTo(300, 0.75);
		this.show();

		// Disallow tabbing through main-page controls during modal window
		// It's not perfect, as clicking in to the browser chrome will allow
		// users to start tabbing through controls again, but it's a start.
		var first = this.find(':input:visible:enabled:first').focus();
		var last = this.find(':input:visible:enabled:last');

		last.on('keydown.modalwindow', function(e) {
			if (e.which == 9) { e.preventDefault(); first.focus(); }
		});

		return this;
	}



	$.fn.closeModal = function () {
		$('#modalwindow_mask').hide();
		this.trigger('modalwindow_close.modalwindow', [modalwindow]);
		this.off('modalwindow_close.modalwindow');
		this.hide();
		return this;
	}



})(jQuery);


