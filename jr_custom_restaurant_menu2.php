<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );



/*

Plugin Name:  Elixir Bistro Custom Menu Creation Plugin

Plugin URI:   https://elixirbistro.ca

Description:  Plugin to generate the custom daily menu  developed for elixir bistro.

Version:      2018/07/08

Author:       Jessica Rudolph

Author URI:   https://www.linkedin.com/in/rudolphjessica

License:      GPL2

License URI:  https://www.gnu.org/licenses/gpl-2.0.html

Text Domain:  jr_custom_restaurant_menu 

Domain Path:  /languages

*/





//Register Custom Menu Category Taxonomy to 

//tie built in use of wordpress category functions 

//with menu feature custom post type



function menu_feature_taxonomy() {  

    register_taxonomy(  

        'menu_feature_categories',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces). 

        'jr_menu_item',        //post type name

        array(  

            'hierarchical' => true,  

            'label' => 'Menu Item Categories',  //Display name

            'query_var' => true,

            'rewrite' => array(

                'slug' => 'menu_category', // This controls the base slug that will display before each term

                'with_front' => false // Don't display the category base before 

            )

        )  

    );  

}  

add_action( 'init', 'menu_feature_taxonomy');







//Create menu item custom post type

function menu_item_setup_post_type() {

    // register the "landscape feature" custom post type

    register_post_type( 'jr_menu_item',  [

                           'labels'      => [

                               'name'          => __('Menu items'),

                               'singular_name' => __('Menu Item'),
							   
                               'add_new' => __('Add New Menu Item'),
                               'add_new_item' => __('Add New Menu Item'),
							   

                           ],

                           'public'      => true,

						   'hierarchical' => false,

						   'description' => 'Menu Items are used to display the daily menu page and special events menu pages.',

                           'has_archive' => 'menu_item',

						   'exclude_from_search' => true,

						   'show_in_nav_menus' => false,
			
						   'can_export' => true,
							'rewrite'            => array( 'slug' => 'menu-item' ),
						   'supports' => array( 'title', 'editor', 'author', 'revisions'),

                       ]);

}




//*************CREATE CUSTOM METABOXES FOR DISPLAYING A MENU ITEM ON THE DAILY MENU PAGE****************//

//Create Custom Metabox for tier 1 pricing

function jr_daily_menu_item_display()

{

    $screens = ['jr_menu_item'];

    foreach ($screens as $screen) {

        add_meta_box(

            'jr_menu_feature_box_id',           // Unique ID

            'Display this item on the daily menu page?:',  // Box title

            'jr_display_daily_menu_item_box_html',  // Content callback, must be of type callable

            $screen,                   // Post type

			'normal', 

			'high'

        );

    }

}

//Initalize display of custom tier pricing metaboxes

add_action('add_meta_boxes', 'jr_daily_menu_item_display', 1);


function jr_display_daily_menu_item_box_html($post)

{

	 wp_nonce_field( basename( __FILE__ ), 'jr_display_daily_menu_item_meta_box_nonce' );

	 

	 $jr_menu_item_price_field_value = get_post_meta($post->ID, '_jr_menu_item_price_meta_key', true);
	 $jr_menu_item_daily_display = get_post_meta($post->ID, '_jr_menu_item_daily_display_meta_key', true);
	

	//echo $lft1_image_field_value['url'];	 

    ?>



    <label for="jr_menu_item_price_field">Price: $</label>
    <input name="jr_menu_item_price_field" type="text" value="<?php echo $jr_menu_item_price_field_value ?>" id="jr_menu_item_price_field" class="postbox"/><br/><br/>
	
	<label for="jr_menu_item_daily_display_field">Display on daily menu page?: </label>
    <input name="jr_menu_item_daily_display_field"  type="checkbox"  <?php checked(1, $jr_menu_item_daily_display, true); ?> value="1" id="jr_menu_item_daily_display_field" class="postbox"/>


	

 <?php


}//close display menu item on daily menu page metabox html

function menu_item_save_postdata($post_id){
	
		// verify meta box nonce
	if ( !isset( $_POST['jr_display_daily_menu_item_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['jr_display_daily_menu_item_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	

	// Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	

	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}

	//Get and sanitize fields values (description, min price, max price, img url)
	$jr_menu_categories_item_price_meta_key_value = sanitize_text_field($_POST['jr_menu_item_price_field']);
	$jr_menu_item_display_meta_key_value = sanitize_text_field($_POST['jr_menu_item_daily_display_field']);
	
		if (current_user_can('edit_others_posts')) {

			if (array_key_exists('jr_menu_item_price_field', $_POST)) {

				update_post_meta(

					$post_id,

					'_jr_menu_item_price_meta_key',

					$jr_menu_categories_item_price_meta_key_value

				);
				

			}
			
			
			if (array_key_exists('jr_menu_item_daily_display_field', $_POST)) {
				update_post_meta(

					$post_id,

					'_jr_menu_item_daily_display_meta_key',

					$jr_menu_item_display_meta_key_value

				);
			}
			
			
		}
	
}

add_action('save_post', 'menu_item_save_postdata');


//***finished custom menu item metabox code**//



if ( is_admin() ){ // admin actions
//Calls the menu item custom post type creation function

	add_action( 'init', 'menu_item_setup_post_type', 1);
	//add_action( 'init', 'jr_special_event_setup_post_type', 1);
	//calls the settings page functionality initalization 
	//add_action( 'admin_init', 'jr_menu_plugin_admin_init' );
	

	
} else {
  // non-admin enqueues, actions, and filters
}

function jr_menu_plugin_admin_init(){
	
	
	 /*add_settings_section creates a “section” of settings.
		The first argument is simply a unique id for the section.
		The second argument is the title or name of the section (to be output on the page).
		The third is a function callback to display the guts of the section itself.
		The fourth is a page name. This needs to match the text we gave to the do_settings_sections function call.
	*/
	 add_settings_section('jrplugin_daily_menu_settings_section', 'Daily Menu Item Display Settings', 'jr_menu_text_options_page', 'manage_daily_menu');
		
	//register_setting Initates saving data functionality
	//1st matches unique name given in do_settings() function call
	//name of options in db given in add_options_page
	 //register_setting( 'jr_manage_daily_menu_options', 'manage_daily_menu', 'jr_menu_plugin_options_validate' );
	 register_setting( 'jr_manage_daily_menu_options','manage_daily_menu', 'jr_menu_plugin_options_validate');
	 
	
	/*
	Handling settings custom field values
	The first argument is simply a unique id for the field.
The second is a title for the field.
The third is a function callback, to display the input box. 
The fourth is the page name that this is attached to (same as the do_settings_sections function call).
The fifth is the id of the settings section that this goes into (same as the first argument to add_settings_section).
	*/
	add_settings_field('jr_menu_items_dailyceasar-salad', ' title', 'jr_menu_plugin_setting_string', 'manage_daily_menu');

	
}
	

function jr_menu_plugin_options_validate($input) { // whitelist options 
 // valideates the settings data
 $newinput['text_string'] = trim($input['text_string']);
	if(!preg_match('/^[0-9]$/i', $newinput['text_string'])) {
	$newinput['text_string'] = '';
	}
	return $newinput;
}

function jr_menu_text_options_page() {
echo '<p>These settings will allow you to easily update which menu items will appear on the daily menu page.</p>';

} 

 

function jr_menu_item_pluginprefix_install() {

    // trigger our function that registers the custom post type

    menu_item_setup_post_type();

 

    // clear the permalinks after the post type has been registered

    flush_rewrite_rules();

}



register_activation_hook( __FILE__, 'jr_menu_item_pluginprefix_install' );
 


function jr_menu_item_register_options_page() {
  //add_options_page('Manage Daily Menu', 'Manage Daily Menu', 'manage_options', 'myplugin', 'jr_menu_item_options_page');
	//add_submenu_page( 'jr_menu_item', 'Manage Daily Menu', 'Manage Daily Menu', 'publish_posts', 'manage_daily_menu', 'jr_menu_item_options_page'); 
add_options_page('Manage Daily Menu', 'Manage Daily Menu','manage_options','manage_daily_menu', 'jr_menu_item_options_page'); 
	
	}

//add_action('admin_menu', 'jr_menu_item_register_options_page');

function jr_menu_item_options_page(){
	
	//content for daily menu settings page goes here
	$options = get_option('manage_daily_menu');
	print_r($options);
	?>
		<div class='wrap'>
		  <?php //screen_icon(); ?>
		  <h2>Daily Menu Settings</h2>
		  <form method="post" action="options.php">
		 
			  <?php
			  //unique identifier used in admin_init action hook
			    settings_fields( 'jr_manage_daily_menu_options' ); 
				//name defined in add_options_page
				do_settings_sections( 'manage_daily_menu' );
			  ?>
			  <h3>Select the Menu Items to Display on the Daily Menu.</h3>
			  <p>Menu items can be added and updated from the menu item icon in the admin menu. Select the items you would like to display on the menu today! The website will automatically sort and siaplay the menu items by category (Strater, Main Course, Dessert)</p>
			 <?php 
				//Get all menu items sorted by category
				$args = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					
					//'orderby' => 'meta_value',
					//'meta_key' => 'menu_category'		
					];

		

				$jrmenu_loop = new WP_Query($args);
				//LOOP through each landscape feature
				while ($jrmenu_loop->have_posts()) {
					
					
					//Use wordpress function to get  all landscape feature post data
					$jrmenu_loop->the_post();
					
					
					//Get the post id
					$menu_feature_id = ( basename(get_the_ID()) );
					//Get the post slug
					$jrMenuSlug = ( basename(get_permalink()) );
					//$menu_selected_value = get_post_meta($post->ID, 'jr_menu_items_daily', true);
					
					//Get this posts category slugs
					$jr_menu_categories = "";

					//$jr_menu_terms = basename(get_the_terms( $menu_feature_id,  'menu_feature_categories' ));
					//$jr_menu_terms_string = implode(" ",$jr_menu_terms);
					
			 ?>
			
				<label for="jr_menu_items_daily<?php echo $jrMenuSlug;  ?>"><?php echo $jrMenuSlug;  ?></label>
				<input type="checkbox"  id="jr_menu_items_daily<?php echo $jrMenuSlug;  ?>" name="jr_menu_items_daily[text_string]" value="<?php echo $menu_feature_id; ?>" />
				
			  <?php  
				}//Close menu item loop
			  //submit_button();
			  

			  ?>
			  <p class="submit">
				<input type="submit" name="Submit" value="Update Options" />
			  </p>
		  </form>
		</div>
<?php
	
}


//Function to include supporting files

function jr_menu_item_scripts() {
   //Include Style Sheet for Quote Calculator 
	wp_enqueue_style('jr_menu_item-styles',  '/wp-content/plugins/jr_custom_restaurant_menu/css/daily_menu_items.css' );

}

add_action( 'wp_enqueue_scripts', 'jr_menu_item_scripts' );

function daily_menu_scripts() {
 
wp_register_script('daily_menu_script', plugins_url('/js/daily_menu_display.js', __FILE__), array('jquery'),'1.1', true);
 
wp_enqueue_script('daily_menu_script');
}
  
add_action( 'wp_enqueue_scripts', 'daily_menu_scripts' );  


function add_async_attribute($tag, $handle) {
    if ( 'my-js-handle' !== $handle )
        return $tag;
    return str_replace( ' src', ' async="async" src', $tag );
}
add_filter('script_loader_tag', 'add_async_attribute', 10, 2);


//Daily Menu ... (Menu du jour)

function jr_daily_specials_menu_display() {
	
	
					
					
	$menu_du_jour_html = '<section>
		<div class="col span_12 section-title" style="    border-bottom: 0px;margin-bottom:0px;padding-bottom:0px;">
		<h1 id="menu-du-jour" style="font-size:48px;line-height:58px;text-align:center;padding-bottom: 10px; border-bottom: 1px solid #e7e7e7;">
			Menu Du Jour <span>Prepared Fresh Daily.</span>
		</h1>
	</div>
	<div class="clearfix"></div>
	<h3 style="text-align:center;margin-top:45px;">Appetizers</h3>';
	
	$argsApp = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Menu du Jour Appetizer',
						)
					)];		
	
	
	$jrmenu_sepcialsApp_loop = new WP_Query($argsApp);
	
	while ($jrmenu_sepcialsApp_loop->have_posts()) {
		$jrmenu_sepcialsApp_loop->the_post();
		
			$jrMenuSpecialsAppItemTitle = ( basename(get_the_title()) );	
			$jrMenuSpecialsAppItemDesc = ( basename(get_the_content()) );	
			$jrMenuSpecialsAppSlug = ( basename(get_permalink()) );	
			
			$menu_du_jour_html .= '<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">
							<h4 class="jr_menu_item_title" for="jr_menu_items_daily'.$jrMenuSpecialsAppSlug.'" style="color:rgb(157, 49, 36);font-weight:bold;">'.$jrMenuSpecialsAppItemTitle.'</h4>
							<p class="jr_daily_menu_item_desc" style="min-height:auto;">'.$jrMenuSpecialsAppItemDesc.'</p> 
						</article>';
	}
	
	$menu_du_jour_html .= '<div class="clearfix"></div>
	<h3 style="text-align:center;margin-top:45px;">Main Courses</h3>';
	
	//Get all menu items in the "starters" category
				$args = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Menu du Jour Mains',
						)
					)];		

				$jrmenu_sepcials_loop = new WP_Query($args);
				
				//LOOP through each menu-du-jour item
				while ($jrmenu_sepcials_loop->have_posts()) {
					//Use wordpress function to get all of this menu items post data
					$jrmenu_sepcials_loop->the_post();
					
					
					//Get the post id and additional variables
					$menu_specials_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuSpecialsSlug = ( basename(get_permalink()) );					
					$jrMenuSpecialsItemTitle = ( basename(get_the_title()) );					
					$jrMenuSpecialsItemDesc = ( basename(get_the_content()) );					
					$jrMenuSpecialsItemPrice = get_post_meta($menu_specials_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuSpecialsItemDailyDisplay = get_post_meta($menu_specials_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					$menu_du_jour_html .= '<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">
							<h4 class="jr_menu_item_title" for="jr_menu_items_daily'.$jrMenuSpecialsSlug.'" style="color:rgb(157, 49, 36);font-weight:bold;">'.$jrMenuSpecialsItemTitle.'<!--<span class="daily_menu_item_price">'.$jrMenuSpecialsItemPrice.'</span>--></h4>
							<p class="jr_daily_menu_item_desc">'.$jrMenuSpecialsItemDesc.'</p> 
						</article>';
				}  
					
					
	$menu_du_jour_html .= '<div class="clearfix"></div>
	<h3 style="text-align:center;margin-top:45px;">Desserts</h3>';
	
	//Get all menu items in the "main-course" category
				$argsDessert = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Dessert',
						)
					)];		

				$jrmenu_Dessert_loop = new WP_Query($argsDessert);
				//Use wordpress function to get all of this menu items post data
					
					//LOOP through each menu item
				while ($jrmenu_Dessert_loop->have_posts()) {					
					$jrmenu_Dessert_loop->the_post();
					
					//Get the post id and additional variables
					$menu_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuSlug = ( basename(get_permalink()) );					
					$jrMenuItemTitle = ( basename(get_the_title()) );					
					$jrMenuItemDesc = ( basename(get_the_content()) );					
					$jrMenuItemPrice = get_post_meta($menu_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuItemDailyDisplay = get_post_meta($menu_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					//check to see if display on featured menu checkbox has been selected.
					if($jrMenuItemDailyDisplay == 1){
						//if the checkbox is selected to display a starter item on the daily menu
						//append to shortcode string
						$menu_du_jour_html .= '
						<article class="jr_daily_menu_item_container_third">
							<h4 class="jr_menu_item_title" style="color: rgb(157, 49, 36);font-weight: bold;">'.$jrMenuItemTitle.'<!--<span class="daily_menu_item_price">'.$jrMenuItemPrice.'</span>--></h4>
							<p class="jr_daily_menu_item_desc">'.$jrMenuItemDesc.'</p>
						</article>';
					}else{						
						//do not display the menu item
					}//close if		
					
					
					
				}//Close menu item loop
				
				
				wp_reset_query();	
	return $menu_du_jour_html.'<div class="clearfix"></div></section>';
	
	

}	

//Initialize jr_daily_menu_display function for shortcode use [JR_Menu_Du_Jour]
add_shortcode('JR_Menu_Du_Jour', 'jr_daily_specials_menu_display'); 


//***Wine LIst Menu Shortcode Start [JR_Menu_Wine_List]**//
function JR_Menu_Wine_List_Display() {		
					
	$menu_wine_html = '<h3 style="text-align:center;margin-top:45px;">Wine by the Bottle Service</h3>
	<h4 style="margin-top:25px;">White Wine By The Bottle</h4>';
	
	//Get all menu items in the "White by the Bottle" category
				$argsByTheBottle = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'White by the Bottle',
						)
					)];		

				$jrmenu_by_the_bottle_loop = new WP_Query($argsByTheBottle);
				//Use wordpress function to get all of this menu items post data
					
					//LOOP through each menu item
				while ($jrmenu_by_the_bottle_loop->have_posts()) {					
					$jrmenu_by_the_bottle_loop->the_post();
					
					//Get the post id and additional variables
					$menuByTheBottleId = ( basename(get_the_ID()) );					//Get the post slug
					$jrByTheBottleSlug = ( basename(get_permalink()) );					
					$jrByTheBottleTitle = ( basename(get_the_title()) );					
					$jrByTheBottleItemDesc = ( basename(get_the_content()) );					
					$jrByTheBottleItemPrice = get_post_meta($menuByTheBottleId, '_jr_menu_item_price_meta_key', true);
					$jrByTheBottleDailyDisplay = get_post_meta($menuByTheBottleId, '_jr_menu_item_daily_display_meta_key', true);
						
						//append to shortcode string
						$menu_wine_html .= '
						<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">
							<h4 class="jr_menu_item_title" style="text-align:left;color: rgb(157, 49, 36);font-weight: bold;">'.$jrByTheBottleTitle.'<span class="daily_menu_item_price" style="color:black;">$'.$jrByTheBottleItemPrice.'</span></h4>
							<p class="jr_daily_menu_item_desc">'.$jrByTheBottleItemDesc.'</p>
						</article>';	
				}//Close menu item loop		
	
				//**Sparkling by the bottle start**//
	$menu_wine_html .= '<div class="clearfix"></div><h4 style="margin-top:25px;">Sparkling Wine By The Bottle</h4>';
	
	//Get all menu items in the "White by the Bottle" category
				$argsByTheBottle = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Sparkling by the Bottle',
						)
					)];		

				$jrmenu_by_the_bottle_loop = new WP_Query($argsByTheBottle);
				//Use wordpress function to get all of this menu items post data
					
					//LOOP through each menu item
				while ($jrmenu_by_the_bottle_loop->have_posts()) {					
					$jrmenu_by_the_bottle_loop->the_post();
					
					//Get the post id and additional variables
					$menuByTheBottleId = ( basename(get_the_ID()) );					//Get the post slug
					$jrByTheBottleSlug = ( basename(get_permalink()) );					
					$jrByTheBottleTitle = ( basename(get_the_title()) );					
					$jrByTheBottleItemDesc = ( basename(get_the_content()) );					
					$jrByTheBottleItemPrice = get_post_meta($menuByTheBottleId, '_jr_menu_item_price_meta_key', true);
					$jrByTheBottleDailyDisplay = get_post_meta($menuByTheBottleId, '_jr_menu_item_daily_display_meta_key', true);
						
						//append to shortcode string
						$menu_wine_html .= '
						<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">
							<h4 class="jr_menu_item_title" style="text-align:left;color: rgb(157, 49, 36);font-weight: bold;">'.$jrByTheBottleTitle.'<span class="daily_menu_item_price" style="color:black;">$'.$jrByTheBottleItemPrice.'</span></h4>
							<p class="jr_daily_menu_item_desc">'.$jrByTheBottleItemDesc.'</p>
						</article>';	
				}//Close menu item loop		
	
	
				//**Red by the bottle start**//
			
	
	
	$menu_wine_html .= '<div class="clearfix"></div><h4 style="margin-top:25px;">Red Wine By The Bottle</h4>';
	
	//Use wordpress function to get all of this menu items post data
						

	
	//Get all menu items in the "White by the Bottle" category
				$argsByTheBottle = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'orderby' => 'name',
							'field' => 'name',
							'terms' => 'Red by the Bottle',
							'parent' => 2
						)
					)];		
	
				
							$childTerms = get_terms(['taxonomy' => 'Red by the Bottle', 'orderby' => 'term_id', 'parent' => 'Red by the Bottle', 'hide_empty' => false]);
							foreach($childTerms as $childTerm)
							{
								$postsWine = get_posts(array('post_status' =>'publish','post_type' => 'jr_menu_item',
										array(
											'taxonomy' => 'Red by the Bottle',
											'field' => 'term_id',
											'terms' => $childTerm->name
										),));
								$menu_wine_html .= '<h3>YY'.$childTerm->name.'</h3>'; 
							}
							
						
	
	

				$jrmenu_by_the_bottle_loop = new WP_Query($argsByTheBottle);
				
					//LOOP through each menu item
				while ($jrmenu_by_the_bottle_loop->have_posts()) {		
				
					
					
					$jrmenu_by_the_bottle_loop->the_post();
	
						
					
					//Get the post id and additional variables
					$menuByTheBottleId = ( basename(get_the_ID()) );					//Get the post slug
					$jrByTheBottleSlug = ( basename(get_permalink()) );					
					$jrByTheBottleTitle = ( basename(get_the_title()) );					
					$jrByTheBottleItemDesc = ( basename(get_the_content()) );					
					$jrByTheBottleItemPrice = get_post_meta($menuByTheBottleId, '_jr_menu_item_price_meta_key', true);
					$jrByTheBottleDailyDisplay = get_post_meta($menuByTheBottleId, '_jr_menu_item_daily_display_meta_key', true);
						
						//append to shortcode string
						$menu_wine_html .= '
						<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">
							<h4 class="jr_menu_item_title" style="text-align:left;color: rgb(157, 49, 36);font-weight: bold;">'.$jrByTheBottleTitle.'<span class="daily_menu_item_price" style="color:black;">$'.$jrByTheBottleItemPrice.'</span></h4>
							<p class="jr_daily_menu_item_desc">'.$jrByTheBottleItemDesc.'</p>
						</article>';	
				}//Close menu item loop		
	
				
	
	//*START PREFERRED POUR**//
	$menu_wine_html .= '<hr/>
	<section>
	<h3 style="text-align:center;margin-top:45px;">Preferred Pour: Glass or 1/2 Litre Wine Service</h3>
	<div class="col span_6"> 
		<h4 style="margin-top:25px;">White</h4>';
	
	$argsPreWhite = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Preferred Pour White',
						)
					)];		
	
	
	$jrmenu_preferred_pour_white_loop = new WP_Query($argsPreWhite);
	
	while ($jrmenu_preferred_pour_white_loop->have_posts()) {
		$jrmenu_preferred_pour_white_loop->the_post();
		
			$jrMenuWineTitle = ( basename(get_the_title()) );	
			$jrMenuWineItemDesc = ( basename(get_the_content()) );	
			$jrMenuWineSlug = ( basename(get_permalink()) );	
			
			$menu_wine_html .= '<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">
							<h4 class="jr_menu_item_title" for="jr_menu_items_daily'.$jrMenuWineSlug.'" style="text-align:left;color:rgb(157, 49, 36);font-weight:bold;">'.$jrMenuWineTitle.'</h4>
							<p class="jr_daily_menu_item_desc" style="min-height:auto;">'.$jrMenuWineItemDesc.'</p> 
						</article>';
	}
	
	$menu_wine_html .= '<div class="clearfix"></div></div>
	<div class="col span_6 col_last"> 
		<h4 style="margin-top:25px;">Red</h4>';
	
			//Get all menu items in the "preferred-pour-white" category
				$args = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Preferred Pour Red',
						)
					)];		

				$jrmenu_preferred_pour_white_loop = new WP_Query($args);
				
				//LOOP through each menu-du-jour item
				while ($jrmenu_preferred_pour_white_loop->have_posts()) {
					//Use wordpress function to get all of this menu items post data
					$jrmenu_preferred_pour_white_loop->the_post();
					
					
					//Get the post id and additional variables
					$menu_specials_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuWineSlug = ( basename(get_permalink()) );					
					$jrMenuWineTitle = ( basename(get_the_title()) );					
					$jrMenuWineItemDesc = ( basename(get_the_content()) );					
					$jrMenuWineItemPrice = get_post_meta($menu_specials_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuWineDailyDisplay = get_post_meta($menu_specials_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					$menu_wine_html .= '<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">
							<h4 class="jr_menu_item_title" for="jr_menu_items_daily'.$jrMenuWineSlug.'" style="text-align:left;color:rgb(157, 49, 36);font-weight:bold;">'.$jrMenuWineTitle.'<!--<span class="daily_menu_item_price">'.$jrMenuWineItemPrice.'</span>--></h4>
							<p class="jr_daily_menu_item_desc">'.$jrMenuWineItemDesc.'</p> 
						</article>';
				}  
					
					
	$menu_wine_html .= '<div class="clearfix"></div>
	</div>
	<div class="clearfix"></div>
	<div class="col span_12"><h4 class="daily_menu_item_price" style="text-align:center;color:black;margin-top: 25px;font-weight:bold;">6oz - $10 or ½ Lt - $26</h4></div>
	      
	<div class="clearfix"></div>';
	
	//**END PREFERRED POUR**//
				
				
	wp_reset_query();				
	return $menu_wine_html.'<div class="clearfix"></div></section>';
	
	

}	

//Initialize jr_daily_menu_display function for shortcode use [JR_Menu_Wine_List]
add_shortcode('JR_Menu_Wine_List', 'JR_Menu_Wine_List_Display');
//***Wine LIst Menu Shortcode End [JR_Menu_Wine_List]**//

////******************START CHALKBOARD VERSION OF FEATURED DAILY MENU ( MENU DU JOUR)*/
//Daily Menu ... (Menu du jour)

function jr_daily_specials_chalkboard_menu_display() {
	
	$argsApp = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Menu du Jour Appetizer',
						)
					)];		
	
	
	$jrmenu_sepcialsApp_loop = new WP_Query($argsApp);
	
					
					
	$menu_du_jour_html = '<section>
		<div id="top_menu_chalkboard_container">
			<div class="col span_5 wpb_column column_container vc_column_container col no-extra-padding" style="border-bottom: 0px;margin-bottom:0px;padding-bottom:0px;">
				<h1 id="menu-du-jour" style="font-size: 60px;line-height: 64px;letter-spacing: 5px;">
					<span class="restaurantName">Elixir Bistro\'s</span>
					
				</h1>
			</div>
			<div class="col span_2 wpb_column column_container vc_column_container col no-extra-padding hideMobile" >
				<div class="chef_background_image">&nbsp;</div>
			</div>
			<div class="col span_5 wpb_column column_container vc_column_container col no-extra-padding" style=" text-align:left;   border-bottom: 0px;margin-bottom:0px;padding-bottom:0px;">
			 <div style="    background-image: url(//elixirbistro.ca/wp-content/plugins/jr_custom_restaurant_menu/images/menduJourStacked.png);
    width: 400px;
    height: 178px;
    background-repeat: no-repeat;
    background-position: top center;
    background-size: 90% 90%;
    margin: auto;"></div>
			</div>
			<div class="clearfix"></div>
		</div>
		<!-- end top menu container-->';
	
	//start bottom
	$menu_du_jour_html .=	'<div id="bottom_menu_chalkboard_container">
	<div class="col span_4 starters_background" style="border-right:5px dotted white;">	
		<h3 class="chalkboard_banner_heading" >Starters</h3>';
	
	while ($jrmenu_sepcialsApp_loop->have_posts()) {
		$jrmenu_sepcialsApp_loop->the_post();
		
			$jrMenuSpecialsAppItemTitle = ( basename(get_the_title()) );	
			$jrMenuSpecialsAppItemDesc = ( basename(get_the_content()) );	
			$jrMenuSpecialsAppSlug = ( basename(get_permalink()) );	
			
			$menu_du_jour_html .= '<article class="jr_daily_chalkboard_menu_item_container">
							<h4 class="jr_menu_item_title">'.$jrMenuSpecialsAppItemTitle.'</h4>
							<p class="jr_daily_menu_item_desc" style="min-height:auto;">'.$jrMenuSpecialsAppItemDesc.'</p> 
						</article>';
	}
	
	$menu_du_jour_html .= '</div><!-- close first bottom-->
	
	<div class="col span_4 mains_background" style="border-right:5px dotted white;">	
		<h3 class="chalkboard_banner_heading">Mains</h3>';
	
	//Get all menu items in the "starters" category
				$args = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Menu du Jour Mains',
						)
					)];		

				$jrmenu_sepcials_loop = new WP_Query($args);
				
				//LOOP through each menu-du-jour item
				while ($jrmenu_sepcials_loop->have_posts()) {
					//Use wordpress function to get all of this menu items post data
					$jrmenu_sepcials_loop->the_post();
					
					
					//Get the post id and additional variables
					$menu_specials_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuSpecialsSlug = ( basename(get_permalink()) );					
					$jrMenuSpecialsItemTitle = ( basename(get_the_title()) );					
					$jrMenuSpecialsItemDesc = ( basename(get_the_content()) );					
					$jrMenuSpecialsItemPrice = get_post_meta($menu_specials_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuSpecialsItemDailyDisplay = get_post_meta($menu_specials_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					$menu_du_jour_html .= '<article class="jr_daily_chalkboard_menu_item_container" >
							<h4 class="jr_menu_item_title" >'.$jrMenuSpecialsItemTitle.'</h4>
							<p class="jr_daily_menu_item_desc">'.$jrMenuSpecialsItemDesc.'</p> 
						</article>';
				}  
					
					
	$menu_du_jour_html .= '</div><!-- close second bottom--> 
	<!--start third bottom-->
	<div class="col span_4 dessert_background">
	<h3 class="chalkboard_banner_heading ">Desserts</h3>';
	
	//Get all menu items in the "main-course" category
				$argsDessert = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Dessert',
						)
					)];		

				$jrmenu_Dessert_loop = new WP_Query($argsDessert);
				//Use wordpress function to get all of this menu items post data
					
					//LOOP through each menu item
				while ($jrmenu_Dessert_loop->have_posts()) {					
					$jrmenu_Dessert_loop->the_post();
					
					//Get the post id and additional variables
					$menu_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuSlug = ( basename(get_permalink()) );					
					$jrMenuItemTitle = ( basename(get_the_title()) );					
					$jrMenuItemDesc = ( basename(get_the_content()) );					
					$jrMenuItemPrice = get_post_meta($menu_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuItemDailyDisplay = get_post_meta($menu_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					//check to see if display on featured menu checkbox has been selected.
					if($jrMenuItemDailyDisplay == 1){
						//if the checkbox is selected to display a starter item on the daily menu
						//append to shortcode string
						$menu_du_jour_html .= '
						<article class="jr_daily_chalkboard_menu_item_container">
							<h4 class="jr_menu_item_title" >'.$jrMenuItemTitle.'</h4>
							<p class="jr_daily_menu_item_desc">'.$jrMenuItemDesc.'</p>
						</article>';
					}else{						
						//do not display the menu item
					}//close if		
					
					
					
				}//Close menu item loop
				
				
				wp_reset_query();	
	return $menu_du_jour_html.'</div><!--end third bottom--></div><div class="clearfix"></div></section>';
	
	

}	

//Initialize jr_daily_menu_display function for shortcode use [JR_Menu_Du_Jour_Chalkboard]
add_shortcode('JR_Menu_Du_Jour_Chalkboard', 'jr_daily_specials_chalkboard_menu_display'); 



//////////*****END CHALKBOARD FEATURED DAILY MENU (MENU DU JOUR )****?




////************START Daily Menu  Shortcode***************///
//Compile daily menu list HTML for shortcode use [JR_Daily_Menu]
function jr_daily_menu_display() {
	

		$todaysMenuDate = date('D F jS'); 	
		
	$jr_featured_menu_html_string = '<section>
		<div class="col span_12 section-title" >
		<h1 id="dinner" style="font-size:48px;line-height:58px;text-align:center;">
			Dinner Menu 
		</h1>
	</div>
		
	</section>';
	
	
	//Display Initial Product Place holder Area   
   $jr_featured_menu_html_string .= '		
	  
	   <div id="daily_menu_wrapper">
	   <section class="daily_menu_container starters" style="    border-right-style: inset;">
	    
		<div class="daily_menu_container_inner">
		 ';
			
			//Display the starters menu title
			$jr_featured_menu_html_string .= '<h3 class="daily_menu_category_heading" style="padding-top: 20px;">Starters</h3>';
			
			
			///****START LEFT SIDE MENU (Menu Items in the "starters" category)***///
			
			//Get all menu items in the "starters" category
				$args = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Starter',
						)
					)];		

				$jrmenu_loop = new WP_Query($args);
				
				//LOOP through each menu item
				while ($jrmenu_loop->have_posts()) {
					
					
					//Use wordpress function to get all of this menu items post data
					$jrmenu_loop->the_post();
					
					
					//Get the post id and additional variables
					$menu_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuSlug = ( basename(get_permalink()) );					
					$jrMenuItemTitle = ( basename(get_the_title()) );					
					$jrMenuItemDesc = ( basename(get_the_content()) );					
					$jrMenuItemPrice = get_post_meta($menu_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuItemDailyDisplay = get_post_meta($menu_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					//check to see if display on featured menu checkbox has been selected.
					if($jrMenuItemDailyDisplay == 1){
						//if the checkbox is selected to display a starter item on the daily menu
						//append to shortcode string
						$jr_featured_menu_html_string .= '
						<article class="jr_daily_menu_item_container">
							<h4 class="jr_menu_item_title" style="font-weight: bold;" for="jr_menu_items_daily'.$jrMenuSlug.'">'.$jrMenuItemTitle.'<!--<span class="daily_menu_item_price">'.$jrMenuItemPrice.'</span>--></h4>
							<p class="jr_daily_menu_item_desc">'.$jrMenuItemDesc.'</p>
						</article>';
					}else{						
						//do not display the menu item
					}//close if		
			 
				}//Close menu item loop
				
				
				
		//close daily_menu_container LEFT SIDE (STARTERS)	
		$jr_featured_menu_html_string .= '</div></section>';
		
		
		///****START RIGHT SIDE MENU (Menu Items in the "main-course" category)***///
		
		$jr_featured_menu_html_string .= '
	   <section class="daily_menu_container mains">
	  
		<div class="daily_menu_container_inner">';
			
			//Display the starters menu title
			$jr_featured_menu_html_string .= '<h3 class="daily_menu_category_heading" style="padding-top: 20px;">Main Course</h3>';
			
			
			//Get all menu items in the "main-course" category
				$argsMainCourse = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Main Course',
						)
					)];		

				$jrmenu_maincourse_loop = new WP_Query($argsMainCourse);
				
				//LOOP through each menu item
				while ($jrmenu_maincourse_loop->have_posts()) {
					
					
					//Use wordpress function to get all of this menu items post data
					$jrmenu_maincourse_loop->the_post();
					
					
					//Get the post id and additional variables
					$menu_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuSlug = ( basename(get_permalink()) );					
					$jrMenuItemTitle = ( basename(get_the_title()) );					
					$jrMenuItemDesc = ( basename(get_the_content()) );					
					$jrMenuItemPrice = get_post_meta($menu_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuItemDailyDisplay = get_post_meta($menu_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					//check to see if display on featured menu checkbox has been selected.
					if($jrMenuItemDailyDisplay == 1){
						//if the checkbox is selected to display a starter item on the daily menu
						//append to shortcode string
						$jr_featured_menu_html_string .= '
						<article class="jr_daily_menu_item_container">
							<h4 class="jr_menu_item_title" style="font-weight:bold;" for="jr_menu_items_daily'.$jrMenuSlug.'">'.$jrMenuItemTitle.'<!--<span class="daily_menu_item_price">'.$jrMenuItemPrice.'</span>--></h4>
							<p class="jr_daily_menu_item_desc">'.$jrMenuItemDesc.'</p>
						</article>';
					}else{						
						//do not display the menu item
					}//close if		
			 
				}//Close menu item loop
				
				
				
		//close daily_menu_container RIGHT SIDE (MAIN COURSE)	
		$jr_featured_menu_html_string .= '</div></section><div class="daily_menu_clearfix"></div>';
		
		//****Start Daily Menu Dessert Menu*******//
		
		/*$jr_featured_menu_html_string .= '<section class="daily_menu_bottom_container">
			<div class="daily_menu_container_inner">';
			
			//Display the starters menu title
			$jr_featured_menu_html_string .= '<h3 class="daily_menu_category_heading">Desserts</h3>';
			
			
			//Get all menu items in the "main-course" category
				$argsDessert = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Dessert',
						)
					)];		

				$jrmenu_Dessert_loop = new WP_Query($argsDessert);
				//Use wordpress function to get all of this menu items post data
					
					//LOOP through each menu item
				while ($jrmenu_Dessert_loop->have_posts()) {					
					$jrmenu_Dessert_loop->the_post();
					
					//Get the post id and additional variables
					$menu_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuSlug = ( basename(get_permalink()) );					
					$jrMenuItemTitle = ( basename(get_the_title()) );					
					$jrMenuItemDesc = ( basename(get_the_content()) );					
					$jrMenuItemPrice = get_post_meta($menu_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuItemDailyDisplay = get_post_meta($menu_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					//check to see if display on featured menu checkbox has been selected.
					if($jrMenuItemDailyDisplay == 1){
						//if the checkbox is selected to display a starter item on the daily menu
						//append to shortcode string
						$jr_featured_menu_html_string .= '
						<article class="jr_daily_menu_item_container_third">
							<h4 class="jr_menu_item_title" for="jr_menu_items_daily'.$jrMenuSlug.'">'.$jrMenuItemTitle.'<!--<span class="daily_menu_item_price">'.$jrMenuItemPrice.'</span>--></h4>
							<p class="jr_daily_menu_item_desc">'.$jrMenuItemDesc.'</p>
						</article>';
					}else{						
						//do not display the menu item
					}//close if		
					
					
					
				}//Close menu item loop
				
		
		$jr_featured_menu_html_string .= '<div class="daily_menu_clearfix"></div></section>';*/
		
		$jr_featured_menu_html_string .= '</div>';
		
		
		
	   
	 //close daily_menu_wrapper
	$jr_featured_menu_html_string .= '<div class="daily_menu_clearfix"></div>';  
   wp_reset_query();	
   //<p class="jr_daily_menu_notes"><span>*</span>Please notify us of any allergies.</p>
   return $jr_featured_menu_html_string;
}	

//Initialize jr_daily_menu_display function for shortcode use [JR_Daily_Menu]
add_shortcode('JR_Daily_Menu', 'jr_daily_menu_display'); 

////************END Daily Menu Shortcode ***************///



////******START GROUP MENU ****////////
function jr_group_menu_display() {
	
	
					
					
	$group_menu_html = '
		<div class="col span_12 section-title" >
		<h1 id="group-menu" style="font-size:48px;line-height:58px;text-align:left;margin-bottom:0px;padding-bottom: 0px; border-bottom: 0px solid #e7e7e7;">
			Group Functions & Parties Sample Menu
		</h1>
		<h2 style="    padding-top: 15px;
    text-align: left;
    font-size: 2em;
    letter-spacing: 1px;">With Brewed coffee or tea. $60 per person plus taxes & gratuity.</h2>
	</div>

	<div id="group_menu_wrapper">
	<div class="daily_menu_container starters" style="border-right-style: inset;">
	    	<div class="clearfix"></div>
		<div class="daily_menu_container_inner" >
	<h3 style="text-align:center;padding-top:20px;">Appetizer Choices</h3>';
	
	$argsGroupApp = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Group Starters',
						)
					)];		
	
	
	$jrmenu_groupApp_loop = new WP_Query($argsGroupApp);
	
	$group_menu_html .= '<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">';
	
	$jrTotalAppCount = $jrmenu_groupApp_loop->found_posts;
	
	$jrmenu_groupApp_loop_count = 0;
	
	while ($jrmenu_groupApp_loop->have_posts()) {
			$jrmenu_groupApp_loop->the_post();
			$jrmenu_groupApp_loop_count++;
			$jrMenuGroupAppItemTitle = ( basename(get_the_title()) );	
			$jrMenuGroupAppItemDesc = ( basename(get_the_content()) );	
			$jrMenuGroupAppSlug = ( basename(get_permalink()) );	
			
			$group_menu_html .= '<h4 class="jr_menu_item_title" style="color:rgb(157, 49, 36);font-weight:bold;">'.$jrMenuGroupAppItemTitle.'</h4>';
			
		
			if($jrTotalAppCount > $jrmenu_groupApp_loop_count){
						
				$group_menu_html .= '<span class="jr_menu_item_title" style="    color: #888;font-weight:bold;float:left;width:100%;clear:both;display:block;">OR</span>';
			
				
			}
	
	}
	
	
		$group_menu_html .= '<div class="clearfix" style="height:70px;"></div>
	<h3 style="text-align:center;">Dessert Choices</h3>';
	
	//Get all menu items in the "main-course" category
				$argsGroupDessert = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Group Dessert',
						)
					)];		

				$jrmenu_Group_Dessert_loop = new WP_Query($argsGroupDessert);
				//Use wordpress function to get all of this menu items post data
				$jrTotalDessertTotalCount = $jrmenu_Group_Dessert_loop->found_posts;
				$jrmenu_groupDessert_loop_count = 0;
					//LOOP through each menu item
				while ($jrmenu_Group_Dessert_loop->have_posts()) {					
					$jrmenu_Group_Dessert_loop->the_post();
					
					//Get the post id and additional variables
					$menu_group_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					//$jrGroupMenuSlug = ( basename(get_permalink()) );					
					$jrGroupMenuItemTitle = ( basename(get_the_title($menu_group_feature_id)) );					
					$jrGroupMenuItemDesc = ( basename(get_the_content($menu_group_feature_id)) );					
					//$jrMenuItemPrice = get_post_meta($menu_group_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuItemDailyDisplay = get_post_meta($menu_group_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					
					
					
					//check to see if display on featured menu checkbox has been selected.
					//if($jrMenuItemDailyDisplay == 1){
						
						$jrmenu_groupDessert_loop_count++;
						//if the checkbox is selected to display a starter item on the daily menu
						//append to shortcode string
						$group_menu_html .= '
						<article class="">
							<h4 class="jr_menu_item_title" style="color: rgb(157, 49, 36);font-weight: bold;">'.$jrGroupMenuItemTitle.'</h4>
						
						</article>';
						
						//every other menu item write OR column
						if($jrTotalDessertTotalCount > $jrmenu_groupDessert_loop_count){
							$group_menu_html .= '<span class="jr_menu_item_title" style="    color: #888;font-weight:bold;float:left;width:100%;clear:both;display:block;">OR</span>';				
						}
						
						
				//	}else{						
						//do not display the menu item
					//}//close if		
					
					
					
				}//Close menu item loop*/
				
				
				
	
	
	
	$group_menu_html .= '</ul><div class="clearfix"></div>
		</div>
	</div>
	
	
	
	<div class="daily_menu_container mains">
	    
		<div class="daily_menu_container_inner">
	<h3 style="text-align:center;margin-top:25px;">Main Course Choices</h3>';
	
	//Get all menu items in the "starters" category
				$argsGroupMains = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Group Main Course',
						)
					)];		

				$jrmenu_groupMain_loop = new WP_Query($argsGroupMains);
				
					$jrTotalGroupMainCount = $jrmenu_groupMain_loop->found_posts;
				
				
				$jrmenu_groupMain_loop_count = 0;
				//LOOP through each menu-du-jour item
				while ($jrmenu_groupMain_loop->have_posts()) {
					//Use wordpress function to get all of this menu items post data
					$jrmenu_groupMain_loop->the_post();
				
				
					$jrmenu_groupMain_loop_count++; 
					 
					//Get the post id and additional variables
					$menu_group_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuGroupSlug = ( basename(get_permalink()) );					
					$jrMenuGroupItemTitle = ( basename(get_the_title()) );					
					$jrMenuGroupItemDesc = ( basename(get_the_content()) );					
					
					$jrMenuGroupItemDailyDisplay = get_post_meta($menu_group_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					$group_menu_html .= '<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">
							<h4 class="jr_menu_item_title"  style="color:rgb(157, 49, 36);font-weight:bold;">'.$jrMenuGroupItemTitle.'</h4>
							<p class="jr_daily_menu_item_desc" style="padding-bottom: 25px;">'.$jrMenuGroupItemDesc.'</p> 
						</article>';
				
					
					//every other menu item write OR column
						if($jrTotalGroupMainCount > $jrmenu_groupMain_loop_count){
							$group_menu_html .= '<span class="jr_menu_item_title" style="    color: #888;font-weight:bold;float:left;width:100%;clear:both;display:block;">OR</span>';				
						} 
				
				
				}  
					
				
				
				
	$group_menu_html .= '</div></div><div class="clearfix"></div></div>';
	wp_reset_query();	
	return $group_menu_html;
	
	

}	

add_shortcode('JR_Group_Menu', 'jr_group_menu_display'); 
//********END GROUP MENU ****/////////

////******START NEW YEARS MENU ****////////
function jr_new_year_menu_display() {
	
	
					
					
	$group_menu_html = '
		<div class="col span_12 section-title" >
		<h1 id="group-menu" style="text-align:center;font-size:48px;line-height:58px;margin-bottom:0px;padding-bottom: 0px; border-bottom: 0px solid #e7e7e7;">
			Our 2018 New Years Eve Menu
		</h1>
		<h2 style="    padding-top: 15px;
    font-size: 2em;
    letter-spacing: 1px;text-align:center;">$70 per person plus taxes & gratuity.</h2>
	</div>

	<div id="group_menu_wrapper">
	<div class="daily_menu_container starters" style="border-right-style: inset;">
	    	<div class="clearfix"></div>
		<div class="daily_menu_container_inner" >
	<h3 style="text-align:center;padding-top:20px;">Starter Choices</h3>';
	
	$argsNewYearspApp = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'First Course',
						)
					)];		
	
	
	$jrmenu_newyearsApp_loop = new WP_Query($argsNewYearspApp);
	
	$group_menu_html .= '<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">';
	
	$jrTotalNewYearAppCount = $jrmenu_newyearsApp_loop->found_posts;
	
	$jrmenu_newyearApp_loop_count = 0;
	
	while ($jrmenu_newyearsApp_loop->have_posts()) {
			$jrmenu_newyearsApp_loop->the_post();
			$jrmenu_newyearApp_loop_count++;
			$jrMenuGroupAppItemTitle = ( basename(get_the_title()) );	
			$jrMenuGroupAppItemDesc = ( basename(get_the_content()) );	
			$jrMenuGroupAppSlug = ( basename(get_permalink()) );	
			
			$group_menu_html .= '<h4 class="jr_menu_item_title" style="color:rgb(157, 49, 36);font-weight:bold;">'.$jrMenuGroupAppItemTitle.'</h4>';
			
		
			if($jrTotalNewYearAppCount > $jrmenu_newyearApp_loop_count){
						
				$group_menu_html .= '<span class="jr_menu_item_title" style="    color: #888;font-weight:bold;float:left;width:100%;clear:both;display:block;">OR</span>';
			
				
			}
	
	}
	
	
		$group_menu_html .= '<div class="clearfix" style="height:70px;"></div>
	<h3 style="text-align:center;">Dessert Choices</h3>';
	
	//Get all menu items in the "main-course" category
				$argsGroupDessert = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Third Course',
						)
					)];		

				$jrmenu_Group_Dessert_loop = new WP_Query($argsGroupDessert);
				//Use wordpress function to get all of this menu items post data
				$jrTotalDessertTotalCount = $jrmenu_Group_Dessert_loop->found_posts;
				$jrmenu_groupDessert_loop_count = 0;
					//LOOP through each menu item
				while ($jrmenu_Group_Dessert_loop->have_posts()) {					
					$jrmenu_Group_Dessert_loop->the_post();
					
					//Get the post id and additional variables
					$menu_group_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					//$jrGroupMenuSlug = ( basename(get_permalink()) );					
					$jrGroupMenuItemTitle = ( basename(get_the_title($menu_group_feature_id)) );					
					$jrGroupMenuItemDesc = ( basename(get_the_content($menu_group_feature_id)) );					
					//$jrMenuItemPrice = get_post_meta($menu_group_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuItemDailyDisplay = get_post_meta($menu_group_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					
					
					
					//check to see if display on featured menu checkbox has been selected.
					//if($jrMenuItemDailyDisplay == 1){
						
						$jrmenu_groupDessert_loop_count++;
						//if the checkbox is selected to display a starter item on the daily menu
						//append to shortcode string
						$group_menu_html .= '
						<article class="">
							<h4 class="jr_menu_item_title" style="color: rgb(157, 49, 36);font-weight: bold;">'.$jrGroupMenuItemTitle.'</h4>
						
						</article>';
						
						//every other menu item write OR column
						if($jrTotalDessertTotalCount > $jrmenu_groupDessert_loop_count){
							$group_menu_html .= '<span class="jr_menu_item_title" style="    color: #888;font-weight:bold;float:left;width:100%;clear:both;display:block;">OR</span>';				
						}
						
						
				//	}else{						
						//do not display the menu item
					//}//close if		
					
					
					
				}//Close menu item loop*/
				
				
				
	
	
	
	$group_menu_html .= '</ul><div class="clearfix"></div>
		</div>
	</div>
	
	
	
	<div class="daily_menu_container mains">
	    
		<div class="daily_menu_container_inner">
	<h3 style="text-align:center;margin-top:25px;">Main Course Choices</h3>';
	
	//Get all menu items in the "second course" category
				$argsGroupMains = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Second Course',
						)
					)];		

				$jrmenu_groupMain_loop = new WP_Query($argsGroupMains);
				
					$jrTotalGroupMainCount = $jrmenu_groupMain_loop->found_posts;
				
				
				$jrmenu_groupMain_loop_count = 0;
				//LOOP through each menu-du-jour item
				while ($jrmenu_groupMain_loop->have_posts()) {
					//Use wordpress function to get all of this menu items post data
					$jrmenu_groupMain_loop->the_post();
				
				
					$jrmenu_groupMain_loop_count++; 
					 
					//Get the post id and additional variables
					$menu_group_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuGroupSlug = ( basename(get_permalink()) );					
					$jrMenuGroupItemTitle = ( basename(get_the_title()) );					
					$jrMenuGroupItemDesc = ( basename(get_the_content()) );					
					
					$jrMenuGroupItemDailyDisplay = get_post_meta($menu_group_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					$group_menu_html .= '<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">
							<h4 class="jr_menu_item_title"  style="color:rgb(157, 49, 36);font-weight:bold;">'.$jrMenuGroupItemTitle.'</h4>
							<p class="jr_daily_menu_item_desc" style="padding-bottom: 25px;">'.$jrMenuGroupItemDesc.'</p> 
						</article>';
				
					
					//every other menu item write OR column
						if($jrTotalGroupMainCount > $jrmenu_groupMain_loop_count){
							$group_menu_html .= '<span class="jr_menu_item_title" style="    color: #888;font-weight:bold;float:left;width:100%;clear:both;display:block;">OR</span>';				
						} 
				
				
				}  
					
				
				
				
	$group_menu_html .= '</div></div><div class="clearfix"></div></div>';
	wp_reset_query();	
	return $group_menu_html;
	
	

}	

add_shortcode('JR_New_Year_Menu', 'jr_new_year_menu_display'); 
//********END new year MENU ****/////////


////******START Valentines Day MENU ****////////
function jr_vday_menu_display() {					
					
	$group_menu_html = '
		<div class="col span_12 section-title" >
		<h1 id="group-menu" style="text-align:center;font-size:48px;line-height:58px;margin-bottom:0px;padding-bottom: 0px; border-bottom: 0px solid #e7e7e7;">
			Our 2019 Valentine\'s Day Menu
		</h1>
		<h2 style="    padding-top: 15px;
    font-size: 2em;
    letter-spacing: 1px;text-align:center;">First Sitting: $55, Second Sitting $60.<br/> prices are per person plus taxes & gratuity.</h2>
	</div> 

	<div id="group_menu_wrapper">
	<div class="daily_menu_container starters" style="border-right-style: inset;">
	    	<div class="clearfix"></div>
		<div class="daily_menu_container_inner" >
	<h3 style="text-align:center;padding-top:20px;">Starter Choices</h3>';
	
	$argsNewYearspApp = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Starter Choices'
						)
					)];		
	
	
	$jrmenu_newyearsApp_loop = new WP_Query($argsNewYearspApp);
	
	$group_menu_html .= '<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">';
	
	$jrTotalNewYearAppCount = $jrmenu_newyearsApp_loop->found_posts;
	
	$jrmenu_newyearApp_loop_count = 0;
	
	while ($jrmenu_newyearsApp_loop->have_posts()) {
			$jrmenu_newyearsApp_loop->the_post();
			$jrmenu_newyearApp_loop_count++;
			$jrMenuGroupAppItemTitle = ( basename(get_the_title()) );	
			$jrMenuGroupAppItemDesc = ( basename(get_the_content()) );	
			$jrMenuGroupAppSlug = ( basename(get_permalink()) );	
			
			$group_menu_html .= '<h4 class="jr_menu_item_title" style="color:rgb(157, 49, 36);font-weight:bold;">'.$jrMenuGroupAppItemTitle.'</h4><p style="margin-bottom:0px;padding-bottom:0px;text-align:center;">'.$jrMenuGroupAppItemDesc.'</p>';
			
		
			if($jrTotalNewYearAppCount > $jrmenu_newyearApp_loop_count){
						
				$group_menu_html .= '<span class="jr_menu_item_title" style="color: #888;font-weight:bold;float:left;width:100%;clear:both;display:block;">OR</span>';
			
				
			}
	
	}
	
	
		$group_menu_html .= '<div class="clearfix" style="height:70px;"></div>
	<h3 style="text-align:center;">Dessert Choices</h3>';
	
	//Get all menu items in the "main-course" category
				$argsGroupDessert = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Dessert Choices',
						)
					)];		

				$jrmenu_Group_Dessert_loop = new WP_Query($argsGroupDessert);
				//Use wordpress function to get all of this menu items post data
				$jrTotalDessertTotalCount = $jrmenu_Group_Dessert_loop->found_posts;
				$jrmenu_groupDessert_loop_count = 0;
					//LOOP through each menu item
				while ($jrmenu_Group_Dessert_loop->have_posts()) {					
					$jrmenu_Group_Dessert_loop->the_post();
					
					//Get the post id and additional variables
					$menu_group_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					//$jrGroupMenuSlug = ( basename(get_permalink()) );					
					$jrGroupMenuItemTitle = ( basename(get_the_title($menu_group_feature_id)) );					
					$jrGroupMenuItemDesc = ( basename(get_the_content($menu_group_feature_id)) );					
					//$jrMenuItemPrice = get_post_meta($menu_group_feature_id, '_jr_menu_item_price_meta_key', true);
					$jrMenuItemDailyDisplay = get_post_meta($menu_group_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					
					
					
					//check to see if display on featured menu checkbox has been selected.
					//if($jrMenuItemDailyDisplay == 1){
						
						$jrmenu_groupDessert_loop_count++;
						//if the checkbox is selected to display a starter item on the daily menu
						//append to shortcode string
						$group_menu_html .= '
						<article class="">
							<h4 class="jr_menu_item_title" style="color: rgb(157, 49, 36);font-weight: bold;">'.$jrGroupMenuItemTitle.'</h4>
						
						</article>';
						
						//every other menu item write OR column
						if($jrTotalDessertTotalCount > $jrmenu_groupDessert_loop_count){
							$group_menu_html .= '<span class="jr_menu_item_title" style="color: #888;font-weight:bold;float:left;width:100%;clear:both;display:block;">OR</span>';				
						}
						
						
				//	}else{						
						//do not display the menu item
					//}//close if		
					
					
					
				}//Close menu item loop*/
				
				
				
	
	
	
	$group_menu_html .= '</ul><div class="clearfix"></div>
		</div>
	</div>
	
	
	
	<div class="daily_menu_container mains">
	    
		<div class="daily_menu_container_inner">
	<h3 style="text-align:center;margin-top:25px;">Main Course Choices</h3>';
	
	//Get all menu items in the "second course" category
				$argsGroupMains = ['post_type'      => 'jr_menu_item',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'menu_feature_categories',
							'field' => 'name',
							'terms' => 'Main Course Choices'
						)
					)];		

				$jrmenu_groupMain_loop = new WP_Query($argsGroupMains);
				
					$jrTotalGroupMainCount = $jrmenu_groupMain_loop->found_posts;
				
				
				$jrmenu_groupMain_loop_count = 0;
				//LOOP through each menu-du-jour item
				while ($jrmenu_groupMain_loop->have_posts()) {
					//Use wordpress function to get all of this menu items post data
					$jrmenu_groupMain_loop->the_post();
				
				
					$jrmenu_groupMain_loop_count++; 
					 
					//Get the post id and additional variables
					$menu_group_feature_id = ( basename(get_the_ID()) );					//Get the post slug
					$jrMenuGroupSlug = ( basename(get_permalink()) );					
					$jrMenuGroupItemTitle = ( basename(get_the_title()) );					
					$jrMenuGroupItemDesc = ( basename(get_the_content()) );					
					
					$jrMenuGroupItemDailyDisplay = get_post_meta($menu_group_feature_id, '_jr_menu_item_daily_display_meta_key', true);
					
					$group_menu_html .= '<article class="jr_daily_menu_item_container" style="height:auto;background-color:rgba(255,255,255,0.03);padding-right:10px;padding-left:10px;">
							<h4 class="jr_menu_item_title"  style="color:rgb(157, 49, 36);font-weight:bold;">'.$jrMenuGroupItemTitle.'</h4>
							<p class="jr_daily_menu_item_desc" style="padding-bottom: 25px;">'.$jrMenuGroupItemDesc.'</p> 
						</article>';
				
					
					//every other menu item write OR column
						if($jrTotalGroupMainCount > $jrmenu_groupMain_loop_count){
							$group_menu_html .= '<span class="jr_menu_item_title" style="color: #888;font-weight:bold;float:left;width:100%;clear:both;display:block;">OR</span>';				
						} 
				
				
				}  
					
				
				
				
	$group_menu_html .= '</div></div><div class="clearfix"></div></div>';
		wp_reset_query();
	return $group_menu_html;
	
	

}	

add_shortcode('JR_VDay_Menu', 'jr_vday_menu_display'); 
//********END Vday MENU ****/////////

//*special event shortcode for side bar*/

////************START Daily Menu  Shortcode***************///
//Compile daily menu list HTML for shortcode use [JR_Daily_Menu]
function jr_special_events_display() {
				
				//Get all menu items in the "main-course" category
				/*$argsMenuEvents = ['post_type'      => 'tribe_events',
					'posts_per_page' => 3,
								    'post_status'  =>  'publish',
					'order' => 'DESC',
					'orderby'=>'start-date'];		

				$jrMenuEvents_loop = new WP_Query($argsMenuEvents);*/
	
$jrMenuEvents =	tribe_get_events( array(
'posts_per_page' => 3,
	'eventDisplay' => 'future',
	 'post_status'  =>  'publish',
) );
				
				//Use wordpress function to get all of this menu items post data
				$special_menu_event_html_string = '<aside class="jr_event_sidebar"><div style="border:2px solid white;padding:25px 10px;">';
				
				
				
				//Hosting an event
				$special_menu_event_html_string .= "<h2 style='text-align:center' >Hosting an Event?</h2>
				<p class='jr_daily_menu_item_desc' style='color: #cb1a26'>
					<a title='link to reservation form' href='/reservations/'>Reserve online</a>, by <a title='Click to call 519-623-2800' href='tel:+1-519-623-2800'>telephone</a>, or ask your server for details in person! We can host your group for holidays and all other event parties.
				</p>";
	
	
				//Events
				//$special_menu_event_html_string .= '<h2 class="jr_event_sidebar_title h1">Upcoming Events</h2>';
				
				foreach($jrMenuEvents as $jrMenuEvents_loop) {					
					//$jrMenuEvents_loop->the_post();
					
					$eventHTMLTAGTYPE = "h2";
					if($jrMenuEvents_loop > 1){
						
						//dISPLAY UPCOMING Events HEADER
						$special_menu_event_html_string .= '<h2 class="jr_event_sidebar_title h1">Upcoming Events</h2>';
						//change article tag to h3
							$eventHTMLTAGTYPE = "h3";
					}
				
					//Get the post id and additional variables
					$jrmenu_event_feature_id = $jrMenuEvents_loop->ID;					//Get the post slug
					$jrmenu_event_slug = $jrMenuEvents_loop->post_permalink;					
					$jrmenu_event_title = $jrMenuEvents_loop->post_title;					
					$jrmenu_event_desc = $jrMenuEvents_loop->post_content;	
					$jrmenu_event_short_desc = $jrMenuEvents_loop->post_excerpt;	
					
					$jrmenu_event_desc = substr($jrmenu_event_desc, 0, 250);
					//$jrmenu_event_desc .= '...<a href="/event/'.$jrmenu_event_slug.'">Read More</a>';
					
					
					//LOOP through each event item
					$special_menu_event_html_string .= '<article style="padding-left:0px;padding-right:0px;margin-left:0px;">';
				
					
					$special_menu_event_html_string .= '<'.$eventHTMLTAGTYPE.' style="text-align:center;margin-bottom: 20px;">'.$jrmenu_event_title.'</'.$eventHTMLTAGTYPE.'>';
					$special_menu_event_html_string .= '<p class="jr_daily_menu_item_desc ">'.$jrmenu_event_short_desc.'</p>';
					$special_menu_event_html_string .= '<a class="fullWidth" title="Click to see the '.$jrmenu_event_title.' Event Menu" href="https://elixirbistro.ca/event/valentines-day/">Click to see our '.$jrmenu_event_title.' ></a>';
					$special_menu_event_html_string .= '</article>';
					
					
				
				}
				
				
				
					/*$special_menu_event_html_string .= '<a class="nectar-button large regular accent-color  regular-button" style="margin-top: 30px; visibility: visible;" href="/events/holidays" data-color-override="false" data-hover-color-override="false" data-hover-text-color-override="#fff"><span>All Events > 
					</span></a>';*/
					
					$special_menu_event_html_string .= '<a class="fullWidth" title="Click to reserve now" href="/reservations/">Reserve your table now ></a>';
					$special_menu_event_html_string .= '</div></aside>';
				wp_reset_query();	
				return $special_menu_event_html_string;
	
	
	
	
}

//Initialize jr_daily_menu_display function for shortcode use [JR_Special_Event_Sidebar]
add_shortcode('JR_Special_Event_Sidebar', 'jr_special_events_display'); 

//flush_rewrite_rules( false );

?>