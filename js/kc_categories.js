/*
 * CPV Code Lookup Script
 * 
 * An <input id="cpv_trigger" ... > tag will set-off an AJAX lookup of relevant CPV codes
 * 
 */



$.require([
	"jquery.scrollTo-min.js" ,
	"jquery.color.js" ,
	"jquery.dump.js" ,
	"ecl.js" ,
]);



var CPV_TIMEOUT = 500;



var cpv_trigger = null;
var cpv_target = null;

var cpv_old_trigger_val = null; 

var cpv_selected_codes = [];



/**
 * Perform a CPV lookup (if required)
 */
function cpvLookup() {
	
	if (cpv_trigger.val() != cpv_old_trigger_val) {

		cpv_old_trigger_val = cpv_trigger.val();
		
		
		/**
		 * Show CPV suggestions as checkboxes
		 * 
		 * @param  object  reply
		 */
		function showSuggestions(reply) {
			
			var template = $('<tr> '
				+'<td class="checkbox"><input type="checkbox" name="cpv_code[]" id="%HTMLID%" value="%ID%" /></td>'
				+'<td class="name"><label for="%HTMLID%" class="normal">%NAME%</label></td>'
				+'<td class="str"><label for="%HTMLID%" class="normal">%ID%</label></td>'
				+'</tr>');
			
			var suggestions = $('<table class="rowhover"></table>');
			$.each(reply.matches, function (k, v) {
				var html_id = 'cpv_sug_' + v.id;
				
				var newEntry = template.clone();
				newEntry.find('input').attr('id', html_id).val(v.id)
					.end().find('td:nth-child(2) label').attr('for', html_id).html(v.name)
					.end().find('td:nth-child(3) label').attr('for', html_id).html('(' + v.id +')')
					.end().appendTo(suggestions);
			});
			
			cpv_target.empty()
				.css("height", "auto")
				.append(suggestions);
			
			return true;
		}// /function
		
		
		
		/**
		 * Show a "no suggestions" message
		 */
		function showNoSuggestions(reply) {
			cpv_target.empty()
				.html('<p>No suggestions found. Try checking the full list for any suitable matches.</p>');
		}// /function
		

		Ecl.showLoading(cpv_target);
		
		
		// Request matching CPV codes
		$.ajax({
			url     : APP_WWW + "/ajax/cpvmatch/" ,
			data    : { q : cpv_trigger.val() } ,
			success : function (reply) {
<<<<<<< HEAD
=======
				//alert($.dump(reply));
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
				if ( ("ok" == reply._status) && (reply.matches) ) {
					showSuggestions(reply);
					return;
				}
				showNoSuggestions(reply);
			} ,
			error   : function (reply) {
				showNoSuggestions(reply);
			} ,
		});// /ajax

	}// if (need lookup)
	
	cpvUpdateSelected();
	
	setTimeout("cpvLookup()", CPV_TIMEOUT);
}// /function



function cpvChangeSelection(id, selected) {
	if (selected) {
		if (cpv_selected_codes.indexOf(id)==-1) {
			cpv_selected_codes.push(id);
		}
	} else {
		cpv_selected_codes = cpv_selected_codes.remove(id);
	}
}// /function



function cpvUpdateSelected() {
	$("#cpv_target input:checkbox:checked").prop("checked", false);
	$("#cpv_listall input:checkbox:checked").prop("checked", false);
	if (cpv_selected_codes.length>0) {
		$.each(cpv_selected_codes, function (k, v) {
			$("#cpv_"+v).prop("checked", true);
			$("#cpv_sug_"+v).prop("checked", true);
		});
	}
}// /function



/**
 * 
 */
function cpvShowLoading(jquery_el) {
	//jquery_el.html('<div class="ecl_loading"><img class="ecl_animated_icon" width="16" height="16" src="'+ APP_WWW +'/images/system/busy_animation.gif" />Loading...</div>');
}// /function



/**
 * Scroll the list of all CPV codes to show the given codew
 */
function cpvScrollListAll(cpv_id) {
	id = "#cpv_" + cpv_id;
	var el = $(id);

	if (undefined != el) {
		$("#cpv_listall").scrollTo(el);
		
		var row = $('tr[data-cpvid="'+ cpv_id +'"] td');
		
		var org_color = row.css("backgroundColor");
		row.stop().animate( { backgroundColor: "#ffaa00" }, 700, function () {
			$(this).animate( { backgroundColor: "#ffffff" }, 700 );
		});
	}
}// /function




/**
 * Document ready...
 */
$(document).ready( function() {
	cpv_trigger = $("input#cpv_trigger");
	cpv_target = $("#cpv_target");
	
	$('#cpv_target input:checkbox').live('click', function() {
		cpvChangeSelection($(this).val(), $(this).prop('checked'));
	});

	$('#cpv_listall input:checkbox').live('click', function() {
		cpvChangeSelection($(this).val(), $(this).prop('checked'));
	});
	
	cpvLookup();
});


