
function changeTabs(id){
	jQuery(".tabs_detail").hide();
	jQuery(".tabs"+id).show();
	jQuery(".nav-tab").removeClass("nav-tab-active");
	jQuery(".nav"+id).addClass("nav-tab-active");
}

jQuery(document).ready(function($) {
	if ($('.set_custom_images').length > 0) {
	    if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
	        $('.wrap').on('click', '.set_custom_images', function(e) {
	            e.preventDefault();
	            var button = $(this);
	            var id = button.prev();
	            wp.media.editor.send.attachment = function(props, attachment) {
	            	if(attachment.mime == 'mmdb'){
	            		$("#geoip_file_name").html(attachment.filename);
	                	$("#geoip_file").val(attachment.url);
	                	console.log(attachment);
	            	}else{
	            		alert('Please upload file type mmdb.');
	            	}
	            	
	            };
	            wp.media.editor.open(button);
	            return false;
	        });
	    }
	};
	
	
	if ($('.set_custom_images_phar').length > 0) {
	    if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
	        $('.wrap').on('click', '.set_custom_images_phar', function(e) {
	            e.preventDefault();
	            var button = $(this);
	            var id = button.prev();
	            wp.media.editor.send.attachment = function(props, attachment) {
					console.log(attachment);
	            	if(attachment.mime == 'application/octet-stream'){
	            		$("#geoip_phar_file_name").html(attachment.filename);
	                	$("#geoip_phar_file").val(attachment.url);
	            	}else{
	            		alert('Please upload file type phar.');
	            	}
	            	
	            };
	            wp.media.editor.open(button);
	            return false;
	        });
	    }
	};
});