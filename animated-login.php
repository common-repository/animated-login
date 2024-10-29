<?php 
/*
* 	Plugin Name: 	Animated Login
* 	Plugin URI:	 	endif.media/portfolio/animated-login
* 	Description: 	Replace the WP logo on the login page with your OWN image, then animate it!
* 	Version: 	 	1.0
*	Author: 	 	ENDif Media
*	Author URI:  	endif.media
*	Text Domain: 	animated-login
*	License:     	GPLv2
*/

/**
 *	Enqueue CSS on Animated Login setting page ONLY 
 *		
 */
function animated_em_add_files_to_adminPage() {
	//get current screen
	$screen_page = get_current_screen();

	//add plugin css ONLY to settings page
	if( 'settings_page_animated-login' == $screen_page->id ){
		wp_enqueue_style( 'animated-admin-css', plugins_url( 'css/plugin-styles.css', __FILE__ ),'20140605', false );
		wp_enqueue_script( 'js-animate', plugins_url('js/admin-animate.js', __FILE__), array( 'jquery' ), '20141905', true );
		wp_enqueue_style( 'animate', plugins_url( 'css/animate.css', __FILE__ ),'20140605', false );
	}
}
add_action( 'admin_enqueue_scripts', 'animated_em_add_files_to_adminPage' );

/**
 * Enqueue jQuery and Animated CSS on login page
 *
 */
function animated_em_add_files_to_loginPage(){		
	wp_enqueue_script( 'jquery' );
	wp_enqueue_style( 'animated-frontend-css', plugins_url( 'css/animate.css', __FILE__ ),'20140605', false );
}
add_action("login_enqueue_scripts", "animated_em_add_files_to_loginPage");

/**
 *	Form for the settings page
 *	
 */
function animated_em_add_jscript(){
	if (get_option( 'animated_em_login_image' )) {
		$add_image = get_option( 'animated_em_login_image' );
		$add_delay = get_option( 'animated_em_delay' );
		$add_class = get_option( 'animated_em_animation_type' );
		//$add_class .=  " animated";
		$bgimage = 'url(' . $add_image . ')'; 
		//$style .= 'background-repeat:none;background-position:scroll center top;height: 133px;background-size: contain';
	} else {
		$bgimage = 'url(' . plugins_url('/img/animated-login-default-img.png', __FILE__) . ')'; 
		$add_class = 'tada';
		$add_delay = '500';
	}
		echo '<script type="text/javascript">
				jQuery("h1").removeClass();
				jQuery("#login h1 a").css({
					"background-image" : "' . $bgimage . '",
					"background-size" : "contain",
					"width" : "100%",
					"background-repeat" : "no-repeat"
				});
	  			jQuery(document).ready(function (){ 
	  				setTimeout(function() {					
						jQuery("#login h1").addClass("' . $add_class .' animated").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(){jQuery(this).removeClass();});				     				    
				    },"' . $add_delay .'");				    					    			    	
				});
				
			  </script>';
}
add_action("login_footer", "animated_em_add_jscript");

/** 
 * Link logo to homepage (instead of wordpress.org)
 *
 */	
function animated_em_link_to_home_page(){
	if( get_option( 'animated_em_link_to_homepage' ) == 1){
		return get_bloginfo('url');		
	} else {
		return 'www.wordpress.org';
	}
}
add_filter( 'login_headerurl', 'animated_em_link_to_home_page' );

/** 
 * Register settings page
 *
 */
function animated_em_create_menu(){
	add_options_page( 'Plugin Settings', 'Animated Login', 'manage_options', 
	'animated-login', 'animated_em_options_page' );
}
add_action( 'admin_menu', 'animated_em_create_menu' );

/**
 * Generate options page
 *	
 */
function animated_em_options_page(){

	//Add support for decimal numbers 
    if (!current_user_can('manage_options')) {
      wp_die( _e('You do not have sufficient permissions to access this page.', 'animated-login') );
    }

    // See if the user has posted us some information
    if( isset($_POST['submit']) ){

    	check_admin_referer( 'animiated_em_save_form', 'animated_em_name_of_nonce' );
 		 
    	$login_image = $_POST["login-image"];
    	$animation_delay = $_POST["animation-delay"];
        $animation_type = $_POST["animation"];
        $link_homepage = isset( $_POST["link-to-homepage"] ) && $_POST["link-to-homepage"] ? "1" : "0";

        // Check if input is a numeric value
        if(intval($animation_delay) != ''){       	
        	$animation_delay = intval($animation_delay);
        	//change $animation_delay into milliseconds 1000
		    // update options        	
        	if($animation_delay < 200  || $animation_delay > 5000) {
        		$animation_em_fail = 'Please choose a number between 200 and 5000 milli-seconds';
		    } else if(!filter_var($login_image, FILTER_VALIDATE_URL)) {
        	  $animation_em_fail = 'Please enter a valid URL';
        	} else {
		     //Everything is cool, update options now
        	 update_option( 'animated_em_login_image', $login_image );
			 update_option( 'animated_em_delay', $animation_delay ); 
			 update_option( 'animated_em_animation_type', $animation_type );
			 update_option( 'animated_em_link_to_homepage', $link_homepage );
			 
			 $animation_em_success = true; //issue success variable 
	        }
	    // Fail if !is a numeric value
	    } else {
	      $animation_em_fail = 'Please enter a NUMBER for Animation Delay.'; 
          // function is open until the end of the form
         // Output Message 
	    //echo "$animation_type";
	  }
?>

<?php if(isset($animation_em_fail)) { ?>
<div class="error">
	<p><strong><?php _e("$animation_em_fail", 'animated-login' ); ?></strong></p>
</div>
<?php } ?>

<?php if(isset($animation_em_success) && $animation_em_success == true) { ?>
<div class="updated">
	<p><strong><?php _e('settings saved.', 'animated-login' ); ?></strong></p>
</div>
<?php } ?>

<?php }

    echo '<div class="wrap">';

    echo "<h2>" . __( 'Animated Login - settings', 'animated-login' ) . "</h2><br>
          <p>" . __( 'Add an image, set the timeout, and choose your Animation.', 'animated-login') ."</p>";

?>
		<form id="animated-login-form" method="post" action="">
		  <?php wp_nonce_field( 'animiated_em_save_form', 'animated_em_name_of_nonce' ); ?>
		  <table class="form-table">		  	 
		    <tr valign="top">
		       <th scope="row"><?php _e( 'Upload Image:', 'animated-login' ); ?></th>
			    <td>
			       <input type="text" id="loginimage" class="js--login" name="login-image" value="<?php print get_option( 'animated_em_login_image' ); ?>" size="70"/><br>
			       <em><?php _e( 'Upload an image to the media library and copy/paste the ENTIRE URL here. Begin with http://', 'animated-login' ); ?></em> 
			    </td>
		    </tr>		    
		    <tr>
		    	<td height="30"></td>
		    </tr>
		    <tr>
		      <?php if(!get_option( 'animated_em_login_image' )) {

		      	// DEFAULT IMAGE HERE
		       	echo '<th scope="row" width="20" id="animated-em-dynamic-text">' . __( 'Your image will replace this one', 'animated-login' ) . '&rarr;</th>
					    <td>
				    	   <div id="td-animated-image"><img style="max-width:60%;border-style:none;" id="animated-em-your-image" src="'. plugins_url() .'/animated-login/img/animated-login-default-img.png" alt="No image yet." border="0" /></div>
				        </td>';

		        } else {

		      
		    	echo '<th scope="row">' . __( 'Your Image', 'animated-login' ) . '&rarr;</th>
					    <td>
				    	   <div id="td-animated-image"><img style="max-width:60%;border-style:none" id="animated-em-your-image" src="' . get_option( 'animated_em_login_image' ) . '" alt="Animated Login Image" border="0" />
				    	   </div>
				        </td>';
		        }
		      ?>
		    </tr>
		    <tr valign="top">
		       <th scope="row"><?php _e( 'Do you want the image to link to the hompage?', 'animated-login' ); ?></th>
			    <td>
			       <input type="checkbox" id="link-to-homepage" name="link-to-homepage" value="1" <?php checked( get_option( 'animated_em_link_to_homepage' ), '1' ); ?> />
			    </td>
		    </tr> 
		    <tr valign="top">
		       <th scope="row"><?php _e( 'Animation Delay:', 'animated-login' ); ?></th>
			    <td>
			       <input type="number" min="200" max="5000" step="any" id="animation-delay" name="animation-delay" value="<?php print get_option( 'animated_em_delay' ); ?>" /><br>
			       <em><?php _e( '200-5000 milli-seconds (hint: 1000 = 1 sec)', 'animated-login'); ?></em>
			    </td>
		    </tr> 		    
		    <tr valign="middle">
			   <th scope="row">
				  <label for="animation"><?php _e( 'Choose Animation:', 'animated-login' ); ?></label>
			   </th>
			   <td>
		  	       <select name="animation" class="js--animations">
					  <option value="bounce" <?php selected( get_option( 'animated_em_animation_type' ), 'bounce' ); ?>><?php _e( 'Bounce', 'animated-login' ); ?></option>
					  <option value="flash" <?php selected( get_option( 'animated_em_animation_type' ), 'flash' ); ?>><?php _e( 'Flash', 'animated-login' ); ?></option>
					  <option value="pulse" <?php selected( get_option( 'animated_em_animation_type' ), 'pulse' ); ?>><?php _e( 'Pulse', 'animated-login' ); ?></option>					 
					  <option value="rubberBand" <?php selected( get_option( 'animated_em_animation_type' ), 'rubberBand' ); ?>><?php _e( 'Rubber Band', '' ); ?></option>					  
					  <option value="shake" <?php selected( get_option( 'animated_em_animation_type' ), 'shake' ); ?>><?php _e( 'Shake', 'animated-login' ); ?></option>
					  <option value="swing" <?php selected( get_option( 'animated_em_animation_type' ), 'swing' ); ?>><?php _e( 'Swing', 'animated-login' ); ?></option>
					  <option value="tada" <?php selected( get_option( 'animated_em_animation_type' ), 'tada' ); ?>><?php _e( 'Tada', 'animated-login' ); ?></option>
					  <option value="wobble" <?php selected( get_option( 'animated_em_animation_type' ), 'wobble' ); ?>><?php _e( 'Wobble', 'animated-login' ); ?></option>
					  <option value="bounceIn" <?php selected( get_option( 'animated_em_animation_type' ), 'bounceIn' ); ?>><?php _e( 'Bounce In', 'animated-login' ); ?></option>
					  <option value="bounceInDown" <?php selected( get_option( 'animated_em_animation_type' ), 'bounceInDown' ); ?>><?php _e( 'Bounce In Down', 'animated-login' ); ?></option>
					  <option value="bounceInLeft" <?php selected( get_option( 'animated_em_animation_type' ), 'bounceInLeft' ); ?>><?php _e( 'Bounce In Left', 'animated-login' ); ?></option>
					  <option value="bounceInRight" <?php selected( get_option( 'animated_em_animation_type' ), 'bounceInRight' ); ?>><?php _e( 'Bounce In Right', 'animated-login' ); ?></option>
					  <option value="bounceInUp" <?php selected( get_option( 'animated_em_animation_type' ), 'bounceInUp"' ); ?>><?php _e( 'Bounce In Up', 'animated-login' ); ?></option>
					  <option value="bounceOut" <?php selected( get_option( 'animated_em_animation_type' ), 'bounceOut' ); ?>><?php _e( 'Bounce Out', 'animated-login' ); ?></option>
					  <option value="bounceOutDown" <?php selected( get_option( 'animated_em_animation_type' ), 'bounceOutDown' ); ?>><?php _e( 'Bounce Out Down', 'animated-login' ); ?></option>
					  <option value="bounceOutLeft" <?php selected( get_option( 'animated_em_animation_type' ), 'bounceOutLeft' ); ?>><?php _e( 'Bounce Out Left', 'animated-login' ); ?></option>
					  <option value="bounceOutRight" <?php selected( get_option( 'animated_em_animation_type' ), 'bounceOutRight' ); ?>><?php _e( 'Bounce Out Right', 'animated-login' ); ?></option>
					  <option value="bounceOutUp" <?php selected( get_option( 'animated_em_animation_type' ), 'bounceOutUp' ); ?>><?php _e( 'Bounce Out Up', 'animated-login' ); ?></option>
					  <option value="fadeIn" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeIn' ); ?>><?php _e( 'Fade In', 'animated-login' ); ?></option>
					  <option value="fadeInDown" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeInDown' ); ?>><?php _e( 'Fade In Down', 'animated-login' ); ?></option>
					  <option value="fadeInDownBig" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeInDownBig' ); ?>><?php _e( 'Fade In Down - Big', 'animated-login' ); ?></option>
					  <option value="fadeInLeft" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeInLeft' ); ?>><?php _e( 'Fade In Left', 'animated-login' ); ?></option>
					  <option value="fadeInLeftBig" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeInLeftBig' ); ?>><?php _e( 'Fade In Left - Big', 'animated-login' ); ?></option>
					  <option value="fadeInRight" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeInRight' ); ?>><?php _e( 'Fade In Right', 'animated-login' ); ?></option>
					  <option value="fadeInRightBig" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeInRightBig' ); ?>><?php _e( 'Fade In Right - Big', 'animated-login' ); ?></option>
					  <option value="fadeInUp" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeInUp' ); ?>><?php _e( 'Fade In Up', 'animated-login' ); ?></option>
					  <option value="fadeInUpBig" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeInUpBig' ); ?>><?php _e( 'Fade In Up - Big', 'animated-login' ); ?></option>
					  <option value="fadeOut" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeOut' ); ?>><?php _e( 'Fade Out', 'animated-login' ); ?></option>
					  <option value="fadeOutDown" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeOutDown' ); ?>><?php _e( 'Fade Out Down', 'animated-login' ); ?></option>
					  <option value="fadeOutDownBig" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeOutDownBig' ); ?>><?php _e( 'Fade Out Down - Big', 'animated-login' ); ?></option>
					  <option value="fadeOutLeft" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeOutLeft' ); ?>><?php _e( 'Fade Out Left', 'animated-login' ); ?></option>
					  <option value="fadeOutLeftBig" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeOutLeftBig' ); ?>><?php _e( 'Fade Out Left - Big', 'animated-login' ); ?></option>
					  <option value="fadeOutRight" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeOutRight' ); ?>><?php _e( 'Fade Out Right', 'animated-login' ); ?></option>
					  <option value="fadeOutRightBig" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeOutRightBig' ); ?>><?php _e( 'Fade Out Right - Big', 'animated-login' ); ?></option>
					  <option value="fadeOutUp" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeOutUp' ); ?>><?php _e( 'Fade Out Up', 'animated-login' ); ?></option>
					  <option value="fadeOutUpBig" <?php selected( get_option( 'animated_em_animation_type' ), 'fadeOutUpBig' ); ?>><?php _e( 'Fade Out Up - Big', 'animated-login' ); ?></option>
					  <option value="flip" <?php selected( get_option( 'animated_em_animation_type' ), 'flip' ); ?>><?php _e( 'Flip', 'animated-login' ); ?></option>
					  <option value="flipInX" <?php selected( get_option( 'animated_em_animation_type' ), 'flipInX' ); ?>><?php _e( 'Flip In - X', 'animated-login' ); ?></option>
					  <option value="flipInY" <?php selected( get_option( 'animated_em_animation_type' ), 'flipInY' ); ?>><?php _e( 'Flip In - Y', 'animated-login' ); ?></option>
					  <option value="flipOutX" <?php selected( get_option( 'animated_em_animation_type' ), 'flipOutX' ); ?>><?php _e( 'Flip Out - X', 'animated-login' ); ?></option>
					  <option value="flipOutY" <?php selected( get_option( 'animated_em_animation_type' ), 'flipOutY' ); ?>><?php _e( 'Flip Out - Y', 'animated-login' ); ?></option>
					  <option value="lightSpeedIn" <?php selected( get_option( 'animated_em_animation_type' ), 'lightSpeedIn' ); ?>><?php _e( 'Light Speed In', 'animated-login' ); ?></option>
					  <option value="lightSpeedOut" <?php selected( get_option( 'animated_em_animation_type' ), 'lightSpeedOut' ); ?>><?php _e( 'Light Speed Out', 'animated-login' ); ?></option>
					  <option value="rotateIn" <?php selected( get_option( 'animated_em_animation_type' ), 'rotateIn' ); ?>><?php _e( 'Rotate In', 'animated-login' ); ?></option>
					  <option value="rotateInDownLeft" <?php selected( get_option( 'animated_em_animation_type' ), 'rotateInDownLeft' ); ?>><?php _e( 'Rotate In Down - Left', 'animated-login' ); ?></option>
					  <option value="rotateInDownRight" <?php selected( get_option( 'animated_em_animation_type' ), 'rotateInDownRight' ); ?>><?php _e( 'Rotate In Down - Right', 'animated-login' ); ?></option>
					  <option value="rotateInUpLeft" <?php selected( get_option( 'animated_em_animation_type' ), 'rotateInUpLeft' ); ?>><?php _e( 'Rotate In Up - Left', 'animated-login' ); ?></option>
					  <option value="rotateInUpRight" <?php selected( get_option( 'animated_em_animation_type' ), 'rotateInUpRight' ); ?>><?php _e( 'Rotate In Up - Right', 'animated-login' ); ?></option>
					  <option value="rotateOut" <?php selected( get_option( 'animated_em_animation_type' ), 'rotateOut' ); ?>><?php _e( 'Rotate Out', 'animated-login' ); ?></option>
					  <option value="rotateOutDownLeft" <?php selected( get_option( 'animated_em_animation_type' ), 'rotateOutDownLeft' ); ?>><?php _e( 'Rotate Out Down - Left', 'animated-login' ); ?></option>
					  <option value="rotateOutDownRight" <?php selected( get_option( 'animated_em_animation_type' ), 'rotateOutDownRight' ); ?>><?php _e( 'Rotate Out Down - Right', 'animated-login' ); ?></option>
					  <option value="rotateOutUpLeft" <?php selected( get_option( 'animated_em_animation_type' ), 'rotateOutUpLeft' ); ?>><?php _e( 'Rotate Out Up - Left', 'animated-login' ); ?></option>
					  <option value="rotateOutUpRight" <?php selected( get_option( 'animated_em_animation_type' ), 'rotateOutUpRight' ); ?>><?php _e( 'Rotate Out Up - Right', 'animated-login' ); ?></option>
					  <option value="slideInDown" <?php selected( get_option( 'animated_em_animation_type' ), 'slideInDown' ); ?>><?php _e( 'Slide In Down', 'animated-login' ); ?></option>
					  <option value="slideInLeft" <?php selected( get_option( 'animated_em_animation_type' ), 'slideInLeft' ); ?>><?php _e( 'Slide In Left', 'animated-login' ); ?></option>
					  <option value="slideInRight" <?php selected( get_option( 'animated_em_animation_type' ), 'slideInRight' ); ?>><?php _e( 'Slide In Right', 'animated-login' ); ?></option>
					  <option value="slideOutLeft" <?php selected( get_option( 'animated_em_animation_type' ), 'slideOutLeft' ); ?>><?php _e( 'Slide Out Left', 'animated-login' ); ?></option>
					  <option value="slideOutRight" <?php selected( get_option( 'animated_em_animation_type' ), 'slideOutRight' ); ?>><?php _e( 'Slide Out Right', 'animated-login' ); ?></option>
					  <option value="slideOutUp" <?php selected( get_option( 'animated_em_animation_type' ), 'slideOutUp' ); ?>><?php _e( 'Slide Out Up', 'animated-login' ); ?></option>
					  <option value="hinge" <?php selected( get_option( 'animated_em_animation_type' ), 'hinge' ); ?>><?php _e( 'Hinge', 'animated-login' ); ?></option>
					  <option value="rollIn" <?php selected( get_option( 'animated_em_animation_type' ), 'rollIn' ); ?>><?php _e( 'Roll In', 'animated-login' ); ?></option>
					  <option value="rollOut" <?php selected( get_option( 'animated_em_animation_type' ), 'rollOut' ); ?>><?php _e( 'Roll Out', 'animated-login' ); ?></option>
				   </select>	
			   </td>
		  </tr>	 
		  </table>
		  <p class="submit">
		   <input type="submit" class="button-primary" name="submit" value="<?php _e('Save Changes') ?>" />
		  </p>
		  <div class="alert alert-warning">
		    <strong><?php _e( 'Hey there!', 'animated-login' ); ?></strong><?php _e( ' If you enjoy this plugin, please ', 'animated-login' ); ?><a href="http://wordpress.org/plugins/animated-login/"><?php _e( 'rate it!', 'animated-login' ) ;?></a>
		  </div>
		</form>
	</div>
<?php } // function animated_em_options_page() closed 