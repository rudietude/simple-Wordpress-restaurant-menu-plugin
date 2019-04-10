jQuery(function($) {
//alert("rawr");
if ($(window).width() > 650) {
	if($("#daily_menu_wrapper").length > 0){
		//if daily menu container exists
		
		//get two menu section heights
		var starterHeight = $("#daily_menu_wrapper .daily_menu_container.starters .daily_menu_container_inner").height();
		
		var mainHeight = $("#daily_menu_wrapper .daily_menu_container.mains .daily_menu_container_inner").height();
		
		//alert(starterHeight);
		//alert(mainHeight);
		if(starterHeight > mainHeight){
				$("#daily_menu_wrapper .daily_menu_container.mains .daily_menu_container_inner").height(starterHeight);
		}else{
			$("#daily_menu_wrapper .daily_menu_container.starters .daily_menu_container_inner").height(mainHeight);
			
		}
	}
	
	
	var group_starterHeight = $("#group_menu_wrapper .daily_menu_container.starters .daily_menu_container_inner").height();
	var group_mainHeight = $("#group_menu_wrapper .daily_menu_container.mains .daily_menu_container_inner").height();
	
	if(group_starterHeight > group_mainHeight){
				$("#group_menu_wrapper .daily_menu_container.mains .daily_menu_container_inner").height(group_starterHeight);
		}else{
			$("#group_menu_wrapper .daily_menu_container.starters .daily_menu_container_inner").height(group_mainHeight);
			
		}
		
} //close if window width
	
	
});
