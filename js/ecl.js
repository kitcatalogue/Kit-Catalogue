


var Ecl = new function () {
	
	this.uniqueId = function () {
		var id = 1;
		return function() {
			return 'unique_' + id++;
		}
	}// /function
	
	
	
	this.showLoading = function (jquery_el) {
		jquery_el.html('<div class="ecl_loading"><img class="ecl_animated_icon" width="16" height="16" src="'+ APP_WWW +'/images/system/busy_animation.gif" />Loading...</div>');
	}// /function
	
	
	
};



// Add Array.indexOf() method for locating items by value (<=IE8 need one)
if(!Array.prototype.indexOf){
    Array.prototype.indexOf = function(item, i){
        i= i || 0;
        var len = this.length;
        while (i< len) {
            if (this[i]=== item) { return i; }
            ++i;
        }
        return -1;
    }
}



if (!Array.prototype.remove) {
	Array.prototype.remove = function(item) {
		var what, a=arguments, len=a.length, ax;
	    while (len && this.length) {
	    	item = a[--len];
	        while ((ax = this.indexOf(item))!= -1) {
	            this.splice(ax, 1);
	        }
	    }
	    return this;
	}
}



// Alter $JQuery.unique() to work with all data types
(function($) {
	
    var _old = $.unique;
 
    $.unique = function(arr) {
        // do the default behavior only if we have an array of elements
        if (!!arr[0].nodeType){
            return _old.apply(this, arguments);
        } else {
            // reduce the array to contain no dupes via grep/inArray
            return $.grep(arr, function(v,k){
                return $.inArray(v, arr) === k;
            });
        }
    };
    
})(jQuery);


