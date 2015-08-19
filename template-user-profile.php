<?php
/*
Template Name: User profile
*/
?>

<?php tutsplus_private_page(); ?>

<?php while (have_posts()) : the_post(); ?>
	<?php get_template_part( 'templates/page', 'header' ); ?>
	<?php get_template_part( 'templates/content', 'page' ); ?>

	<?php do_action( 'tutsplus_process_user_profile' ); ?>
	<?php
	/**
 	* Get's the user info
 	* Returned in an object
 	* http://codex.wordpress.org/Function_Reference/get_userdata
 	*/
	$user_id 	= get_current_user_id();
	$user_info 	= get_userdata( $user_id );
	?>

	<form role="form" action="" id="user_profile" method="POST">
		<?php wp_nonce_field('user_profile_nonce', 'user_profile_nonce_field'); ?>
		<div class="form-group">
			<label for="first_name">First name</label>
			<input type="text" class="form-control" id="first_name" name="first_name" placeholder="First name" value="<?php echo $user_info->first_name; ?>">
		</div>
		<div class="form-group">
			<label for="last_name">Last name</label>
			<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last name" value="<?php echo $user_info->last_name; ?>">
		</div>
		<div class="form-group">
			<label for="twitter_name">Twitter name</label>
			<input type="text" class="form-control" id="twitter_name" name="twitter_name" placeholder="Twitter name" value="<?php echo $user_info->twitter_name; ?>">
		</div>
		<div class="form-group">
			<label for="email">Email address</label>
			<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" value="<?php echo $user_info->user_email; ?>">
		</div>
		<div class="form-group">
			<label for="pass1">Password</label>
			<input type="password" class="form-control" id="pass1" name="pass1" placeholder="Password">
		</div>
		<div class="form-group">
			<label for="pass2">Repeat Password</label>
			<input type="password" class="form-control" id="pass2" name="pass2" placeholder="Password">
		</div>
		<button type="submit" class="btn btn-default">Submit</button>
	</form>

<?php endwhile; ?>
