


function getResults() {
	$.ajax({
		url: '../filter/Results/',
		data: $('#filterby').serialize(),
		success: function(data) {
			$('#resultspace').html(data);
		}
	});
}

function updateSelects() {
	$.ajax({
		url: '../filter/JSON',
		data: $('#filterby').serialize(),
		success: function(data) {
			var options = '';
			makeSelections(data.results.departments,'department_selector');
			makeSelections(data.results.manufacturers,'manufacturer_selector');
			makeSelections(data.results.techniques,'technique_selector');
			makeSelections(data.results.categorys,'category_selector');
			makeSelections(data.results.buildings,'building_selector');
			makeSelections(data.results.tags,'tag_selector');
		}
	});
}

function makeSelections (dataset,whichSelect) {
	var options = '';
	var selector = '#' + whichSelect;
	for (var i=0, option; option=dataset[i]; ++i) {
		options += '<option value="' + option[0] + '"';
		if (option[3]==1) options += ' selected';
		options += '>' + option[1] + '(' + option[2] + ')</option>';
	}
	$(selector).html(options);
}

function makeSearchboxContent(heading,pass,val,id){
	var str="";
	var identifyMe = heading + '__' + id.replace(" ","_");
	// belt-and-braces - js is really that bad!
	identifyMe = identifyMe.replace(" ","_");
	identifyMe = identifyMe.replace("&","_and_");
	
	heading = heading.substring(0, 1).toUpperCase() + heading.substring(1).toLowerCase();
	str = '<div id="' + identifyMe + '">';
	if (pass==0) {
		str += heading + '<br />';	
	}
	str += '<li><span>' + val + '</span> <a href="#" onclick="removeFromSearch(\'' + identifyMe + '\')">Remove</a></li>';
	str += '</div>';
	return str;
  }

function removeFromSearch(id) {
	var split = id.split("__");
	$('#' + split[0] + '_selector option').attr('selected', false);
    $('#' + id).remove();
	$("#search-criteria").animate({
        height: $("#grow-me").height() + $("#clearAll").height()
    },600);
	getResults();
    updateSelects();
}

function removeAllFromSearch() {
	$('#grow-me').empty();
	$('form select option').prop('selected',false);
	$("#search-criteria").animate({
        height: $("#grow-me").height() + $("#clearAll").height()
    },600);
	$('#compound-search').empty();
	getResults();
	updateSelects();
}