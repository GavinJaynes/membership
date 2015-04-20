<?php
/**
 * Contains all functions that aid with customising the 
 * WordPress admin - The split of this code might not be 100% logical
 * but for the purpose of the series it's nice to have the code in one file
 */ 


/** ==================================================================
 * PART ONE OF THE SERIES
 */

/**
 * Style the wordpress login/register screens
 * Uses conditonal loading of stylesheets 
 */ 
function admin_css() {
	
	if ( $_GET['action'] === 'register' ) {

		wp_enqueue_style( 'register_css', get_template_directory_uri() . '/admin/css/register-form.css' );

	} else {

		wp_enqueue_style( 'login_css', get_template_directory_uri() . '/admin/css/custom-login.css' );
	}
}
add_action('login_head', 'admin_css');


/**
 * Change the link so the the replaced WP logo links to the site
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/login_headerurl
 */
function the_url( $url ) {

    return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'the_url' );



/**
 * Change the link so the the replaced WP logo links to the site
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/login_headerurl
 */
function register_intro_edit($message) {
	
	if (strpos($message, 'Register') !== FALSE) {

		$register_intro = "Become a member. It's easy! Fill in the form below.";

		return '<p class="message register">' . $register_intro . '</p>';

	} else {

		return $message;
	}
}
add_action('login_message', 'register_intro_edit');


/** ==================================================================
 * PART TWO OF THE SERIES
 */

function register_form_edit() {

	$twitter_name = ( ! empty( $_POST['twitter_name'] ) ) ? trim( $_POST['twitter_name'] ) : ''; ?>
    <p>
        <label for="twitter_name">
        	<?php _e( 'Twitter name', 'roots' ) ?><br />
        	<input type="text" name="twitter_name" id="twitter_name" class="input" value="<?php echo esc_attr( wp_unslash( $twitter_name ) ); ?>" size="25" />
        </label>
    </p>

	<?php $terms = ( ! empty( $_POST['terms'] ) ) ? $_POST['terms'] : ''; ?>
    <p>
        <label for="terms">
        	<input type="checkbox" name="terms" id="terms" class="input" value="agreed" <?php checked( $_POST['terms'], 'agreed', true ); ?> />
        	<?php _e( 'I have read the terms and conditions', 'roots' ) ?>
        </label>
    </p>
    <?php
}
add_action( 'register_form', 'register_form_edit' );

//2. Add validation. In this case, we make sure twitter_name is required.
function validate_registration( $errors, $sanitized_user_login, $user_email ) {

	if ( empty( $_POST['twitter_name'] ) || !empty( $_POST['twitter_name'] ) && trim( $_POST['twitter_name'] ) == '' ) {

		$errors->add( 'twitter_name_error', __( '<strong>ERROR</strong>: Please enter your Twitter name.', 'roots' ) );
	}

	if ( preg_match('/[^a-z_\-0-9]/i', $_POST['twitter_name']) ) {

		$errors->add( 'twitter_name_error', __( '<strong>ERROR</strong>: Please use letters, numbers, spaces and underscores only.', 'roots' ) );
	}

	if ( empty( $_POST['terms'] ) ) {

		$errors->add( 'terms_error', __( '<strong>ERROR</strong>: You must agree to the terms.', 'roots' ) );
	}

	return $errors;
}
add_filter( 'registration_errors', 'validate_registration', 10, 3 );

/**
 * Process the additional fields
 *
 * @param user_id
 */
function process_registration( $user_id ) {

	if ( ! empty( $_POST['twitter_name'] ) ) {

		update_user_meta( $user_id, 'twitter_name', trim( $_POST['twitter_name'] ) );
	}

	if ( ! empty( $_POST['terms'] ) ) {

		update_user_meta( $user_id, 'terms', trim( $_POST['terms'] ) );
	}
}
add_action( 'user_register', 'process_registration' );


/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function redirect_on_login( $redirect_to, $request, $user ) {
	//is there a user to check?
	global $user;
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check for admins
		if ( in_array( 'administrator', $user->roles ) ) {
			// redirect them to the default place
			return $redirect_to;

		} else {
			
			return home_url('profile');
		}
	} else {
		return $redirect_to;
	}
}
add_filter( 'login_redirect', 'redirect_on_login', 10, 3 );




/** ==================================================================
 * PART THREE OF THE SERIES
 */
// Display in the wp backend 

//http://codex.wordpress.org/Plugin_API/Action_Reference/show_user_profile


/**
 * Show custom user profile fields
 * @param  obj $user The user object.
 * @return void
 */
function custom_user_profile_fields($user) {
?>
<table class="form-table">
<tr>
	<th>
		<label for="twitter_name"><?php _e('Twitter'); ?></label>
	</th>
	<td>
		<input type="text" name="twitter_name" id="twitter_name" value="<?php echo esc_attr( get_user_meta( $user->ID, 'twitter_name', true ) ); ?>" class="regular-text" />
		<br><span class="description"><?php _e('Twitter name', 'roots'); ?></span>
	</td>
</tr>
<?php get_user_meta( $user->ID, 'twitter_name'); ?>
</table>
<?php
}
add_action('show_user_profile', 'custom_user_profile_fields');
add_action('edit_user_profile', 'custom_user_profile_fields');
