<?php
/**
 * Contains all the front end facing functions that aid membership 
 *
 */ 

/**
 * Makes pages where this function is called only 
 * accesible if you are logged in
 */
function private_page() {

	if(!is_user_logged_in()){

		wp_redirect(home_url());
		exit();
	}
}

/**
 * Outputs some user specific nvaigation 
 * 
 */
function user_nav() {

	$user_id = get_current_user_id();
	$user_name = get_user_meta( $user_id, 'first_name', true);

	echo '<ul class="nav navbar-nav navbar-right">';

	if(is_user_logged_in()) {

		echo '<li><a href="' .home_url('profile').'">Welcome '.$user_name.'</a></li>';
		echo '<li><a href="' .wp_logout_url( home_url() ).'">Log out</a></li>';

	} else {

		echo '<li><a href="' .wp_login_url(). '">Log in</a></li>';
	}

	echo '</ul>';
}

/**
 * Stop subscribers from accessing the backed
 * Also turn off the admin bar for anyone but administrators 
 */
function lock_it_down() {

	if (!current_user_can('administrator') && !is_admin()) {

  		show_admin_bar(false);
	}

	if (current_user_can('subscriber') && is_admin()) {

  		wp_safe_redirect('profile');
	}
}
add_action('after_setup_theme', 'lock_it_down');

/**
 * Pocess the profile editor form
 */
function process_user_profile_data() {

	if( isset($_POST['user_profile_nonce_field']) && wp_verify_nonce($_POST['user_profile_nonce_field'], 'user_profile_nonce')) {

		// Get the current user id
		$user_id = get_current_user_id();

		// Put our data into a better looking array and sanitize it 
		$user_data = array(

			'first_name' 	=> sanitize_text_field($_POST['first_name']),
			'last_name' 	=> sanitize_text_field($_POST['last_name']),
			'user_email' 	=> sanitize_email($_POST['email']),
			'twitter_name' 	=> sanitize_text_field($_POST['twitter_name']),
			'user_pass' 	=> $_POST['pass1'],
			);
		
		if(!empty($user_data['user_pass'])) {

			// Validate the passwords to check they are the same
			if (strcmp($user_data['user_pass'], $_POST['pass2']) !== 0) {

				wp_redirect('?password-error=true');
				exit();
			}

		} else {
			// If the password fields are not set don't save
			unset($user_data['user_pass']);
		}

		// Save the values to the post
		foreach($user_data as $key => $value) {
			
			// http://codex.wordpress.org/Function_Reference/wp_update_user
			if($key == 'twitter_name') {

				$user_id = update_user_meta( $user_id, $key, $value );
				unset($user_data['twitter_name']);

			} elseif ($key == 'user_pass') {
				
				$user_id = wp_set_password( $user_data['user_pass'], $user_id ); 
				unset($user_data['user_pass']);
			
			// Save the remaining values	
			} else {

				$user_id = wp_update_user( array( 'ID' => $user_id, $key => $value ) );
			}
		}

		// Display the messages error/success
	   	if (!is_wp_error( $user_id )){
	      
	      wp_redirect('?profile-updated=true');

	   	} else {
	 
	      wp_redirect("?profile-updated=false");
	   	}
	 	exit;
	}
}
add_action('process_user_profile','process_user_profile_data');


/**
 * Display the correct message based on the query string
 */
function display_messages( $content ) {

	if( $_GET['profile-updated'] == 'true' ) : 

	$message = get_message('Your information was succesfully updated', 'success' );

	elseif( $_GET['profile-updated'] == 'false' ) :

	$message = get_message('There was an error processing your request', 'danger' );	

	elseif( $_GET['password-error'] == 'true' ) :	

	$message = get_message('The passwords you provided did not match', 'danger' );	
 
	endif;	

	return $message . $content;

}
add_filter('the_content','display_messages', 1);

/**
 * A little helper function to get the Bootstrap alerts
 */
function get_message( $string, $type ) {

	$output .= '<div class="alert alert-'.$type.' alert-dismissable">';
	$output .= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-times-circle"></i></button>';
	$output .= '<p class="text-center">'.$string.'</p>';
	$output .= '</div>';

	return $output;
}