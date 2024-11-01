
function changeTabs(id){
	jQuery(".tabs_detail").hide();
	jQuery(".tabs"+id).show();
	jQuery(".nav-tab").removeClass("nav-tab-active");
	jQuery(".nav"+id).addClass("nav-tab-active");
}