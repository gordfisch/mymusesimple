var my_modal = (function(){
    var 
    method = {}

    // Center the modal in the viewport
    method.center = function (target) {
		var top, left;
		
    	top = jQuery(window).height() / 2  - jQuery("#my_modal").outerHeight() / 2;
    	 
    	left = jQuery(window).width() / 2  - jQuery("#my_modal").outerWidth() / 2;
    	
    	jQuery("#my_modal").css({
        	top:top, 
        	left:left
    	});
	};

    // Open the my_modal
    method.open = function (settings) {
		
		jQuery("#my_content").empty().append(settings.content);

    	jQuery("#my_modal").css({
        	width: settings.width || "auto", 
        	height: settings.height || "auto"
   		})

    	method.center(settings.target);

    	jQuery(window).bind('resize.my_modal', method.center);
    	jQuery("#my_overlay").show();
		jQuery("#my_modal").show();
		jQuery("#my_modal").delay(2000).fadeOut(1000,method.close);
	};
		

    // Close the my_modal
    method.close = function () {
		jQuery("#my_modal").hide();
    	jQuery("#my_overlay").hide();
    	jQuery("#my_content").empty();
    	jQuery(window).unbind('resize.my_modal');
    	//location.reload();
	};


    return method;
}());


jQuery(document).ready(function($){
	$("#my_modal").hide();
	$("#my_overlay").hide();
	jQuery("#my_close").click(function(e){
		e.preventDefault();
		my_modal.close();
	})
	jQuery("#my_overlay").click(function(e){
		e.preventDefault();
		my_modal.close();
	})
});
