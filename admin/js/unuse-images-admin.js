(function( $ ) {
	'use strict';
	// alert('unuse-images-admin.js');

	/**
	* All of the code for your admin-facing JavaScript source
	* should reside in this file.
	*
	* Note: It has been assumed you will write jQuery code here, so the
	* $ function reference has been prepared for usage within the scope
	* of this function.
	*
	* This enables you to define handlers, for when the DOM is ready:
	*
	* $(function() {
	*
	* });
	*
	* When the window is loaded:
	*
	* $( window ).load(function() {
	*
	* });
	*
	* ...and/or other possibilities.
	*
	* Ideally, it is not considered best practise to attach more than a
	* single DOM-ready or window-load handler for a particular page.
	* Although scripts in the WordPress core, Plugins and Themes may be
	* practising this, we should strive to set a better example in our own work.
	*/

    let paged = 0;

	jQuery('.unuse-image-scan').on('click',function(){
		var table_media = jQuery('table.media_page_unuse_images');
		jQuery('.progress').width('0%');
		jQuery('.progress-bar').show();
		// table_media.css('opacity','0.4');
		// var data = [];
		// for (var i = 0; i < 10; i++) { // Reduced to 1000 for performance reasons
		// 	var tmp = [];
		// 	for (var j = 0; j < 10; j++) { // Use a different variable name
		// 		tmp[j] = 'hue';
		// 	}
		// 	data[i] = tmp;
		// }
		
		console.log('unuse-image-scan');
		// paged++;
		jQuery.ajax({
			
			type: "POST",
			url: ajax_script.ajaxurl,
			// contentType: false,
            // processData:false,
			// dataType : "json",
			data:{
				'action': 'unuse_image_scan',
				// 'select_depart' : filterDate.select_depart,
				// 'select_loc' : filterDate.select_loc,
				// 'paged' : paged
			},
			xhr: function(){
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function (evt) {
					if (evt.lengthComputable) {
						var percentComplete = (evt.loaded / evt.total) * 100;
						jQuery('.progress').width(percentComplete + '%');
						jQuery(".progress").html(Math.round(percentComplete) +'%');

						// console.log(percentComplete);
						// jQuery('.progress').css({
						// 	width: percentComplete * 100 + '%'
						// });
						// if (percentComplete === 1) {
						// 	jQuery('.progress').addClass('hide');
						// }
					}
				}, false);
				// xhr.addEventListener("progress", function (evt) {
				// 	if (evt.lengthComputable) {
				// 		var percentComplete = evt.loaded / evt.total;
				// 		console.log(percentComplete );
				// 		jQuery('.progress').css({
				// 			width: percentComplete * 100 + '%'
				// 		});
				// 	}
				// }, false);
				return xhr;
			},
		
			beforeSend: function(){
                jQuery(".progress").width('0%');
				jQuery('.progress').html('0%');
                jQuery('.progress-bar').show();
            },
			success:function(response){
				console.log(response);
	        	// table_media.removeAttr("style");
				jQuery('.progress').width('100%');
				setTimeout(function() {
				  jQuery('.progress-bar').hide();
				}, 1000);
				// window.location.reload();
			},
			error: function  (xhr, ajaxOptions, thrownError) {
				console.log(xhr.status);
				console.log(thrownError); 
			}
		});

	});

})( jQuery );
