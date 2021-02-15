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
		add_action( 'init', array( $this, 'luxco_create_docs_taxonomies'), 0 );
		
		add_shortcode( 'member_sidebar', array( $this, 'luxco_member_sidebar') );
		add_shortcode( 'list_documents', array( $this, 'luxco_list_documents') );

		add_action( 'wp_ajax_nopriv_get_docs', array( $this, 'luxco_get_docs' ) );
		add_action( 'wp_ajax_get_docs', array( $this, 'luxco_get_docs' ) );
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
		wp_localize_script( 'custom-js', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
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

	/**
	 * Create taxonomy, type for the post type "Document".
	 *
	 */
	function luxco_create_docs_taxonomies() {
	    $labels = array(
	        'name'              => _x( 'Types', 'taxonomy general name', 'textdomain' ),
	        'singular_name'     => _x( 'Type', 'taxonomy singular name', 'textdomain' ),
	        'search_items'      => __( 'Search Types', 'textdomain' ),
	        'all_items'         => __( 'All Types', 'textdomain' ),
	        'parent_item'       => __( 'Parent Type', 'textdomain' ),
	        'parent_item_colon' => __( 'Parent Type:', 'textdomain' ),
	        'edit_item'         => __( 'Edit Type', 'textdomain' ),
	        'update_item'       => __( 'Update Type', 'textdomain' ),
	        'add_new_item'      => __( 'Add New Type', 'textdomain' ),
	        'new_item_name'     => __( 'New Type Name', 'textdomain' ),
	        'menu_name'         => __( 'Type', 'textdomain' ),
	    );
	 
	    $args = array(
	        'hierarchical'      => true,
	        'labels'            => $labels,
	        'show_ui'           => true,
	        'show_admin_column' => true,
	        'query_var'         => true,
	        'rewrite'           => array( 'slug' => 'type' ),
	    );
	 
	    register_taxonomy( 'type', array( 'document' ), $args );
	}

	public function luxco_send_notification_post_created($post_id, $post, $update){
		$slug = 'document';
    	if($slug   != $post->post_type) return;
    	$user		= get_userdata($user_id);
		$to 		= 'lijo@mr-digital.co.uk'; 
		$subject 	= __('Luxco document uploaded'); 
		$message 	= 'Dear Customer, <br> Luxco has been uploaded a new document.'; 
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

	public function luxco_member_sidebar(){
		ob_start();
		if( ! is_user_logged_in() ) return;
		$user = wp_get_current_user();
		//print_r($user);
		if ( ! in_array( 'customer', $user->roles ) ) return;
		$logo  	  = get_user_meta( $user->ID, 'logo', true );
		$company  = get_user_meta( $user->ID, 'company_name', true );
		$location = get_user_meta( $user->ID, 'location', true );
		$contact  = get_user_meta( $user->ID, 'contact_number', true );
		$nxtVisit = get_field( 'next_visit', 'user_'.$user->ID );
		$contract = get_field( 'contract_period', 'user_'.$user->ID );
		$contract = $contract['start'].' - '.$contract['end'];
		if(!empty($logo)) {
			?><figure class="company-logo">
				<?php echo wp_get_attachment_image($logo, 'full'); ?>
			</figure><?php
		}
		?><ul class="company-details">
			<?php if(!empty($company)) {
				echo '<li>
					<span class="icon-Company"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span></span>
					<p>'.__('Company Name').'</p>
					<b>
					'.$company.'
					</b>
				</li>';
				echo '<li>
					<span class="icon-Location"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span>
					<p>'.__('Location').'</p>
					<b>
					'.$location.'
					</b>
				</li>';
				echo '<li>
					<span class="icon-Envelope"><span class="path1"></span><span class="path2"></span></span>
					<p>'.__('Email Address').'</p>
					<b>
					'.$user->user_email.'
					</b>
				</li>';
				echo '<li>
					<span class="icon-Contact"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span>
					<p>'.__('Contact Number').'</p>
					<b>
					'.$contact.'
					</b>
				</li>';
				echo '<li>
					<span class="icon-Contract-period"><span class="path1"></span><span class="path2"></span></span>
					<p>'.__('Contract Period').'</p>
					<b>
					'.$contract.'
					</b>
				</li>';
				echo '<li>
					<span class="icon-Next-Visit-Date"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span></span>
					<p>'.__('Next Visit Date').'</p>
					<b>
					'.$nxtVisit.'
					</b>
				</li>';
			}?>
		</ul><?php
		return ob_get_clean();
	}

	public function luxco_list_documents(){
		ob_start();
		$terms = get_terms( array(
		    'taxonomy' => 'type',
		    'hide_empty' => false,
		) );
		if( ! empty($terms) ){
			$i = 0;
			echo '<ul class="nav nav-pills" id="pills-tab" role="tablist">';
			echo '<li class="nav-item" role="presentation" id="all">';
			echo '<a class="nav-link active" id="pills-all-tab" data-toggle="pill" href="#pills-all" role="tab" aria-controls="pills-all" aria-selected="true">All Documents</a>';
			echo '</li>';
			foreach($terms as $term){
				$cssClass = 'nav-link';
				echo '<li  class="nav-item" role="presentation" id="'.$term->term_id.'">';
				echo '<a class="nav-link" id="pills-'.$term->term_id.'-tab" data-toggle="pill" href="#pills-'.$term->term_id.'" role="tab" aria-controls="pills-'.$term->term_id.'" aria-selected="true">';
				echo $term->name;
				echo '</a>';
				echo '</li>';
			}
			echo '<li class="nav-item" role="presentation" id="contract">';
			echo '<a class="nav-link" id="pills-contract-tab" data-toggle="pill" href="#pills-contract" role="tab" aria-controls="pills-contract" aria-selected="true">Contracts</a>';
			echo '</li>';
			echo '</ul>';
			echo '<div class="tab-content" id="pills-tabContent">';
			echo '<div class="tab-pane fade show active" id="pills-all" role="tabpanel" aria-labelledby="pills-all-tab">';
			echo '<h3 class="align-left">All Documents</h3>';
			echo '<div class="align-right">Sort files by: 
			<select name="doc-sorter">
				<option value="date">Date</option>
				<option value="title">Name</option>
			</select>
			</div>';
			echo '<div class="clearfix"></div>';
			$args = array(
			    'post_type' => 'document',
			    'showposts' => -1
			);
			$query = new WP_Query( $args );
			if($query->have_posts()){
				echo '<ul class="documents">';
				while($query->have_posts()){
					echo '<li class="'.implode(' ',get_post_class()).'">';
					$query->the_post();
					if(!empty(get_field('preview')))
						echo '<figure><img src="'.get_field('preview').'" alt="'.get_the_title().'" /></figure>';
					echo '<h4>'.get_the_title().'</h4>';
					if(!empty(get_field('pdf'))){
						$pdf = get_field('pdf');
						echo '<div class="doc-actions">
						<a href="#" class="print-doc"><span class="icon-Print"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span></a>
						<a href="'.$pdf['url'].'" class="download-doc" download><span class="icon-Download"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span></a>
						</div>';
					}
					echo '</li>';
				}
				echo '</ul>';
			}
			wp_reset_query();	
			echo '</div>';
			foreach($terms as $term){
				$cssClass = 'tab-pane fade';
				echo '<div class="'.$cssClass.'" id="pills-'.$term->term_id.'" role="tabpanel" aria-labelledby="pills-'.$term->term_id.'-tab">';
				echo '<h3 class="align-left">'.$term->name.'</h3>';
				echo '<div class="align-right">Sort files by: 
				<select name="doc-sorter" data-term="'.$term->slug.'">
					<option value="date">Date</option>
					<option value="title">Name</option>
				</select>
				</div>';
				echo '<div class="clearfix"></div>';
				$args = array(
				    'post_type' => 'document',
				    'tax_query' => array(
				        array(
				            'taxonomy' => 'type',
				            'field'    => 'slug',
				            'terms'    => $term->slug,
				        ),
				    ),
				);
				$query = new WP_Query( $args );
				if($query->have_posts()){
					echo '<ul class="documents">';
					while($query->have_posts()){
						echo '<li class="'.implode(' ',get_post_class()).'">';
						$query->the_post();
						if(!empty(get_field('preview')))
							echo '<figure><img src="'.get_field('preview').'" alt="'.get_the_title().'" /></figure>';
						echo '<h4>'.get_the_title().'</h4>';
						if(!empty(get_field('pdf'))){
							$pdf = get_field('pdf');
							echo '<div class="doc-actions">
							<a href="#" class="print-doc"><span class="icon-Print"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span></a>
							<a href="'.$pdf['url'].'" class="download-doc" download><span class="icon-Download"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span></a>
							</div>';
						}
						echo '</li>';
					}
					echo '</ul>';
				}
				wp_reset_query();
				echo '</div>';
			}
			echo '<div class="tab-pane fade" id="pills-contract" role="tabpanel" aria-labelledby="pills-contract-tab">';
			echo '<h3 class="align-left">Contract</h3>';
			echo '<div class="clearfix"></div>';
		
			echo '</div>';
			echo '</div>';
		}
		?><?php
		return ob_get_clean();
	}

	public function luxco_get_docs(){
		$html = '';
		$taxonomy = array();
		$args = array(
		    'post_type' => 'document',
			'order'   => 'ASC',
		    'showposts' => -1,
		    'orderby'	=> $_POST['sortby'],
		);
		if($_POST['taxonomy']) 
			$args = array(
			    'post_type' => 'document',
			    'showposts' => -1,
			    'order'   => 'ASC',
		    	'orderby'	=> $_POST['sortby'],
			    'tax_query' => array(
			        array(
			            'taxonomy' => 'type',
			            'field'    => 'slug',
			            'terms'    => $_POST['taxonomy'],
			        ),
			    ),
			);
		$query = new WP_Query( $args );
		if($query->have_posts()){
			while($query->have_posts()){
				$html .= '<li class="'.implode(' ',get_post_class()).'">';
				$query->the_post();
				if(!empty(get_field('preview')))
					$html .= '<figure><img src="'.get_field('preview').'" alt="'.get_the_title().'" /></figure>';
				$html .= '<h4>'.get_the_title().'</h4>';
				if(!empty(get_field('pdf'))){
					$pdf = get_field('pdf');
					$html .= '<div class="doc-actions">
					<a href="#" class="print-doc"><span class="icon-Print"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span></a>
					<a href="'.$pdf['url'].'" class="download-doc" download><span class="icon-Download"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span></a>
					</div>';
				}
				$html .= '</li>';
			}
		}
		wp_send_json($html);
		die;
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
