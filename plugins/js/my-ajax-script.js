function uniqueuxcontentaction(type,group,points){
	jQuery.ajax({
		url:uniqueux_ajax_object.ajax_url,
		data: {
			action:'uniqueux_update_points',
			type:type,
			group:group,
			points:points 
		},
		type:"POST",
		success: function(result){
			console.log('success');
			console.log(result);
		}
	});
}