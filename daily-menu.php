<?php 

// =============================================================================
// TEMPLATE NAME: Daily Layout - Sidebar Left, Content Right
// -----------------------------------------------------------------------------
 get_header();
 //echo get_the_content();
//echo do_shortcode('[JR_Daily_Menu]'); 


	
echo '<div class="x-main full" role="main">

    
      <article id="post-6787 " class="post-6787  page type-page status-publish hentry no-post-thumbnail">
        

<div class="entry-content content">


  <div id="cs-content" class="cs-content"><div id="lunch" class="x-section" style="margin: 0px;padding: 0px; background-color: transparent;">
  <div class="x-container max width" style="margin: 0px auto;padding: 0px;">
  <div class="x-column x-sm x-1-1" style="padding: 0px;">
  <h1 class="h-custom-headline cs-ta-center h1" style="color: hsl(0, 0%, 0%);font-family: poor_richardregular;letter-spacing:1px;text-transform:capitalize;"><span>';
  
	echo get_the_title();
  echo '</span></h1>
 
  <hr class="x-gap" style="margin: 50px 0 0 0;"></div>
  </div>
  <div class="x-container max width" style="margin: 0px auto;padding: 0px;">
  <div class="x-column x-sm x-1-3" style="padding: 0px;">
	<aside class="jr_event_sidebar_container">';
		echo do_shortcode('[JR_Special_Event_Sidebar]');
echo '</aside>
			
 </div>
 <div class="x-column x-sm x-2-3" >
	';
	
	echo do_shortcode('[JR_Daily_Menu]');
	
	echo '<hr class="el94 x-line">

	
	
	
	</div></div></div></div>
  

</div>

      </article>

    
  ';
  

 x_get_view( 'footer', 'base' ); 
 ?>