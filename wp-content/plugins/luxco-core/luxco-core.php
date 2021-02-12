<?php
/**
 * Plugin Name:       Luxco Core
 * Description:       Cutomizations for Luxco
 * Plugin URI:        https://mr-digital.co.uk
 * Version:           1.0.0
 * Author:            Lijo
 * Author URI:        https://mr-digital.co.uk
 * Requires at least: 3.0.0
 * Tested up to:      5.6.1
 *
 * @package Luxco_Customisations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Luxco_Customisations Class
 *
 * @class Luxco_Customisations
 * @version	1.0.0
 * @since 1.0.0
 * @package	Luxco_Customisations
 */
final class Luxco_Customisations {

	/**
	 * Set up the plugin
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'luxco_customisations_setup' ), -1 );
		add_image_size('doc_preview', 176, 229, true);
		require_once( 'custom/functions.php' );
	}

	/**
	 * Setup all the things
	 */
	public function luxco_customisations_setup() {
		add_action( 'wp_enqueue_scripts', array( $this, 'luxco_customisations_css' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'luxco_customisations_js' ) );
		add_filter( 'template_include',   array( $this, 'luxco_customisations_template' ), 11 );
		add_filter( 'wc_get_template',    array( $this, 'luxco_customisations_wc_get_template' ), 11, 5 );
		add_action( 'profile_update',  	  array( $this, 'luxco_profile_admin_update' ), 10, 2 );
		add_action( 'init', 			  array( $this, 'luxco_custom_post_type' ));
		add_action(	'save_post', 		  array( $this, 'luxco_send_notification_post_created' ), 10, 3);
		add_action( 'rcp_after_password_registration_field', array( $this, 'luxco_add_user_fields' ) );
		add_action( 'rcp_profile_editor_after', array( $this, 'luxco_add_user_fields' ) );
		add_action( 'rcp_edit_member_after', array( $this, 'luxco_add_member_edit_fields' ) );
		add_action( 'rcp_user_profile_updated', array( $this, 'luxco_save_user_fields_on_profile_save'), 10 );
		add_action( 'rcp_edit_member', array( $this, 'luxco_save_user_fields_on_profile_save'), 10 );
	}

	/**
	 * Enqueue the CSS
	 *
	 * @return void
	 */
	public function luxco_customisations_css() {
		wp_enqueue_style( 'custom-css', plugins_url( '/custom/style.css', __FILE__ ) );
	}

	/**
	 * Enqueue the Javascript
	 *
	 * @return void
	 */
	public function luxco_customisations_js() {
		wp_enqueue_script( 'custom-js', plugins_url( '/custom/custom.js', __FILE__ ), array( 'jquery' ) );
	}

	/**
	 * Look in this plugin for template files first.
	 * This works for the top level templates (IE single.php, page.php etc). However, it doesn't work for
	 * template parts yet (content.php, header.php etc).
	 *
	 * Relevant trac ticket; https://core.trac.wordpress.org/ticket/13239
	 *
	 * @param  string $template template string.
	 * @return string $template new template string.
	 */
	public function luxco_customisations_template( $template ) {
		if ( file_exists( untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/custom/templates/' . basename( $template ) ) ) {
			$template = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/custom/templates/' . basename( $template );
		}

		return $template;
	}

	/**
	 * Look in this plugin for WooCommerce template overrides.
	 *
	 * For example, if you want to override woocommerce/templates/cart/cart.php, you
	 * can place the modified template in <plugindir>/custom/templates/woocommerce/cart/cart.php
	 *
	 * @param string $located is the currently located template, if any was found so far.
	 * @param string $template_name is the name of the template (ex: cart/cart.php).
	 * @return string $located is the newly located template if one was found, otherwise
	 *                         it is the previously found template.
	 */
	public function luxco_customisations_wc_get_template( $located, $template_name, $args, $template_path, $default_path ) {
		$plugin_template_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/custom/templates/woocommerce/' . $template_name;

		if ( file_exists( $plugin_template_path ) ) {
			$located = $plugin_template_path;
		}

		return $located;
	}

	public function luxco_profile_admin_update($user_id, $old_user_data){
		$user		= get_userdata($user_id);
		$to 		= $user->user_email; 
		$subject 	= __('Luxco pofile has been updated'); 
		$message 	= 'Dear '.$user->first_name.'<br>Your profile has been updated.'; 
		$headers[]  = 'Content-Type: text/html; charset=UTF-8';
		$headers[]  = 'From: '.get_bloginfo('name').' <'.get_bloginfo('admin_email').'>';
		wp_mail( $to, $subject, $message, $headers );
	}

	public function luxco_custom_post_type() {
	    register_post_type('document',
	        array(
	            'labels'      => array(
	                'name'          => __('Documents', 'luxco'),
	                'singular_name' => __('Document', 'luxco'),
	            ),
                'public'      => true,
                'has_archive' => true,
	        )
	    );
	}

	public function luxco_send_notification_post_created($post_id, $post, $update){
		$slug = 'document';
    	if($slug   != $post->post_type) return;
    	$user		= get_userdata($user_id);
		$to 		= 'lijo@mr-digital.co.uk'; 
		$subject 	= __('Luxco document uploaded'); 
		$message 	= 'Dear Customer, <br> Luxco has been a new document.'; 
		$headers[]  = 'Content-Type: text/html; charset=UTF-8';
		$headers[]  = 'From: '.get_bloginfo('name').' <'.get_bloginfo('admin_email').'>';
		wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Adds the custom fields to the registration form and profile editor
	 *
	 */
	public function luxco_add_user_fields() {
		
		$company  = get_user_meta( get_current_user_id(), 'company_name', true );
		$location = get_user_meta( get_current_user_id(), 'location', true );
		$contact  = get_user_meta( get_current_user_id(), 'contact_number', true );

		?>
		<p>
			<label for="company_name"><?php _e( 'Company Name', 'luxco' ); ?></label>
			<input name="company_name" id="company_name" type="text" value="<?php echo esc_attr( $company ); ?>"/>
		</p>
		<p>
			<label for="location"><?php _e( 'Your Location', 'luxco' ); ?></label>
			<input name="location" id="location" type="text" value="<?php echo esc_attr( $location ); ?>"/>
		</p>
		<p>
			<label for="contact"><?php _e( 'Contact Number', 'luxco' ); ?></label>
			<input name="contact" id="contact" type="text" value="<?php echo esc_attr( $contact ); ?>"/>
		</p>
		<?php
	}
	
	/**
	 * Adds the custom fields to the member edit screen
	 *
	 */
	function luxco_add_member_edit_fields( $user_id = 0 ) {
		
		$company  = get_user_meta( get_current_user_id(), 'company_name', true );
		$location = get_user_meta( get_current_user_id(), 'location', true );
		$contact  = get_user_meta( get_current_user_id(), 'contact_number', true );

		?>
		<tr valign="top">
			<th scope="row" valign="top">
				<label for="company_name"><?php _e( 'Company', 'rcp' ); ?></label>
			</th>
			<td>
				<input name="company_name" id="company_name" type="text" value="<?php echo esc_attr( $company ); ?>"/>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" valign="top">
				<label for="location"><?php _e( 'Location', 'rcp' ); ?></label>
			</th>
			<td>
				<input name="location" id="location" type="text" value="<?php echo esc_attr( $location ); ?>"/>
				<p class="description"><?php _e( 'The member\'s location', 'rcp' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Stores the information submitted profile update
	 *
	 */
	function luxco_save_user_fields_on_profile_save( $user_id ) {

		if( ! empty( $_POST['company_name'] ) ) {
			update_user_meta( $user_id, 'company_name', sanitize_text_field( $_POST['company_name'] ) );
		}

		if( ! empty( $_POST['location'] ) ) {
			update_user_meta( $user_id, 'location', sanitize_text_field( $_POST['location'] ) );
		}

		if( ! empty( $_POST['contact_number'] ) ) {
			update_user_meta( $user_id, 'contact_number', sanitize_text_field( $_POST['contact_number'] ) );
		}

		$user		= get_userdata($user_id);
		$to 		= get_bloginfo('admin_email'); 
		$subject 	= __('Luxco user pofile has been updated'); 
		$message 	= 'Admin, <br>'.$user->first_name.' updated updated their profile.'; 
		$headers[]  = 'Content-Type: text/html; charset=UTF-8';
		$headers[]  = 'From: '.get_bloginfo('name').' <'.get_bloginfo('admin_email').'>';
		wp_mail( $to, $subject, $message, $headers );

	}

} // End Class

/**
 * The 'main' function
 *
 * @return void
 */
function luxco_customisations_main() {
	new Luxco_Customisations();
}

/**
 * Initialise the plugin
 */
add_action( 'plugins_loaded', 'luxco_customisations_main' );
