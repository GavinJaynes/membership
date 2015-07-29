<?php
/**
 * Contains all the front end facing functions that aid membership
 *
 */

/**
 * Makes pages where this function is called only
 * accesible if you are logged in
 */
function tutsplus_private_page() {

	if(!is_user_logged_in()){

		wp_redirect(home_url());
		exit();
	}
}

/**
 * Stop subscribers from accessing the backed
 * Also turn off the admin bar for anyone but administrators
 */
function tutsplus_lock_it_down() {

	if (!current_user_can('administrator') && !is_admin()) {

		show_admin_bar(false);
	}

	if (current_user_can('subscriber') && is_admin()) {

		wp_safe_redirect('profile');
	}
}
add_action('after_setup_theme', 'tutsplus_lock_it_down');

/**
 * Outputs some user specific nvaigation
 *
 */
function tutsplus_user_nav() {

	$user_id = get_current_user_id();
	$user_name = get_user_meta( $user_id, 'first_name', true );
	$welcome_message = __( 'Welcome', 'sage' ) . ' ' . $user_name;

	echo '<ul class="nav navbar-nav navbar-right">';

		if ( is_user_logged_in() ) {

		echo '<li>';
			echo '<a href="' . home_url( 'profile') . '">' . $welcome_message . '</a>';
		echo '</li>';
		echo '<li>';
			echo '<a href="' . wp_logout_url( home_url() ) . '">' . __( 'Log out', 'sage' ) . '</a>';
		echo '</li>';

		} else {

		echo '<li>';
			echo '<a href="' . wp_login_url() . '">' . __( 'Log in', 'sage' ) . '</a>';
		echo '</li>';

	}

	echo '</ul>';
}

/**
 * Pocess the profile editor form
 */
function tutsplus_process_user_profile_data() {

	if( isset($_POST['user_profile_nonce_field']) && wp_verify_nonce($_POST['user_profile_nonce_field'], 'user_profile_nonce')) {

		// Get the current user id
		$user_id = get_current_user_id();

		// Put our data into a better looking array and sanitize it
		$user_data = array(

			'first_name' 	=> sanitize_text_field($_POST['first_name']),
			'last_name' 	=> sanitize_text_field($_POST['last_name']),
			'user_email' 	=> sanitize_email($_POST['email']),
			'twitter_name'	=> sanitize_text_field($_POST['twitter_name']),
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
add_action('tutsplus_process_user_profile','tutsplus_process_user_profile_data');


/**
 * Display the correct message based on the query string.
 *
 * @param string $content Post content.
 * @return string Message and content.
 */
function tutsplus_display_messages( $content ) {

	if( 'true' == $_GET['profile-updated']  ) :

	$message = tutsplus_get_message_markup('Your information was succesfully updated', 'success' );

	elseif( 'false' == $_GET['profile-updated'] ) :

	$message = tutsplus_get_message_markup('There was an error processing your request', 'danger' );

	elseif( 'true' == $_GET['password-error'] ) :

	$message = tutsplus_get_message_markup('The passwords you provided did not match', 'danger' );

	endif;

	return $message . $content;

}
add_filter('the_content','tutsplus_display_messages', 1);

/**
 * A little helper function to generate the Bootstrap alerts markup.
 *
 * @param string $message Message to display.
 * @param string $severity Severity of message to display.
 * @return string Message markup.
 */
function tutsplus_get_message_markup( $message, $severity ) {

	$output = '<div class="alert alert-' . $severity . ' alert-dismissable">';
			$output .= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">';
					$output .= '<i class="fa fa-times-circle"></i>';
			$output .= '</button>';
			$output .= '<p class="text-center">' . $message . '</p>';
	$output .= '</div>';

	return $output;
}
