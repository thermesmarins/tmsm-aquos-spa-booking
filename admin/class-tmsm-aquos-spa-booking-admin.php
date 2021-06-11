<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://github.com/nicomollet/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/admin
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquos_Spa_Booking_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-aquos-spa-booking-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquos-spa-booking-admin.js', array( 'jquery' ), $this->version, true );

	}

	/**
	 * Register Customer Order Status "Appointment"
	 *
	 * @param array $order_statuses
	 *
	 * @return array
	 */
	function order_status_appointment_register( $order_statuses ) {

		// Status must start with "wc-"
		$order_statuses['wc-appointment'] = array(
			'label'                     => _x( 'Appointment', 'Order status', 'tmsm-aquos-spa-booking' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Appointment <span class="count">(%s)</span>', 'Appointments <span class="count">(%s)</span>', 'tmsm-aquos-spa-booking' ),
		);
		return $order_statuses;
	}

	/**
	 * Name Customer Order Status "Appointment"
	 *
	 * @param string[] $order_statuses
	 *
	 * @return string[]
	 */
	function order_status_appointment_name( $order_statuses ) {
		$order_statuses['wc-appointment'] = _x( 'Appointment', 'Order status', 'tmsm-aquos-spa-booking' );
		return $order_statuses;
	}

	/**
	 * Displays recipient item meta on order page
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @param mixed         $formatted_meta
	 * @param WC_Order_Item $order_item
	 *
	 * @return mixed
	 */
	public function woocommerce_order_item_get_formatted_meta_data( $formatted_meta, WC_Order_Item $order_item ) {
		if ( empty( $formatted_meta ) ) {
			return $formatted_meta;
		}

		foreach ( $formatted_meta as $meta ) {

			if($meta->key == '_appointment' && !empty($meta->value)){
				$meta->display_key = __('Appointment', 'tmsm-aquos-spa-booking');
			}
			if ( $meta->key == '_has_voucher' ) {
				$meta->display_key = __('Has Voucher', 'tmsm-aquos-spa-booking');
				$meta->display_value = ( $meta->value ? __( 'Yes', 'tmsm-aquos-spa-booking' ) : __( 'No', 'tmsm-aquos-spa-booking' ) );
			}
			if($meta->key == '_aquos_id' && !empty($meta->value)){
				$meta->display_key = __('Aquos Product ID', 'tmsm-aquos-spa-booking');
			}
			if($meta->key == '_appointment_processed' && !empty($meta->value)){
				$meta->display_key = __('Appointment Processed to Aquos', 'tmsm-aquos-spa-booking');
				$meta->display_value = ( $meta->value == 'yes' ? __( 'Yes', 'tmsm-aquos-spa-booking' ) : __( 'No', 'tmsm-aquos-spa-booking' ) );
			}
			if($meta->key == '_aquos_price' && !empty($meta->value)){
				$meta->display_key = __('Aquos Price', 'tmsm-aquos-spa-booking');
			}
		}

		return $formatted_meta;
	}

	/**
	 * Hide order item meta from Order
	 *
	 * @since 1.0.0
	 *
	 * @param array $item_meta
	 *
	 * @return array $item_meta
	 */
	public function woocommerce_hidden_order_itemmeta_appointment($item_meta){
		$item_meta[] = '_appointment_date';
		$item_meta[] = '_appointment_time';

		return $item_meta;
	}

	/**
	 * Include settings class
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	function woocommerce_get_settings_pages($settings) {


		$settings[] = include( plugin_dir_path( dirname( __FILE__ ) ) .'includes/class-tmsm-aquos-spa-booking-settings.php' );
		return $settings; // Return
	}



	/**
	 * Add Aquos ID Field to Inventory Product Data Tab
	 */
	public function woocommerce_product_options_inventory_product_data_fields(){
		echo '<div class="options_group">';

		woocommerce_wp_checkbox(
			array(
				'id'          => '_bookable',
				'label'       => __( 'Bookable', 'tmsm-aquos-spa-booking' ),
				'description' => __( 'Check this box if the product is bookable.', 'tmsm-aquos-spa-booking' ),
				//'value'         => $product_object->get_sold_individually( 'edit' ) ? 'yes' : 'no',
			)
		);

			woocommerce_wp_text_input(
				array(
					'id'          => '_aquos_id',
					'label'       => __( 'Aquos Product ID (for appointments for products without choice and without options like course)', 'tmsm-aquos-spa-booking' ),
					'placeholder' => '',
					'desc_tip'    => 'true',
					'required'    => 'true',
					'description' => __( 'If empty, the product won\'t be bookable', 'tmsm-aquos-spa-booking' )
				)
			);

		echo '</div>';
		echo '<div class="options_group">';
		woocommerce_wp_textarea_input(
			array(
				'id'          => '_aquos_items_ids',
				'label'       => __( 'Aquos Products IDs (for appointment packages)', 'tmsm-aquos-spa-booking' ),
				'placeholder' => '',
				'desc_tip'    => 'true',
				'rows'    => 8,
				'description' => __( 'One item per line, write the Aquos product ID separated by an *. Result should be: product name * product id', 'tmsm-aquos-spa-booking' )
			)
		);
		echo '</div>';
		echo '<div class="options_group hide_if_variable">';
		woocommerce_wp_text_input(
			array(
				'id'          => '_aquos_price',
				'label'       => __( 'Aquos Product Price (for web service synchronization)', 'tmsm-aquos-spa-booking' ),
				'custom_attributes' => ['disabled' => 'disabled'],
			)
		);
		echo '</div>';
	}

	/**
	 * Save Product Variation Data
	 *
	 * @since 1.0.0
	 *
	 * @param $variation_id
	 *
	 * @return void
	 */
	function woocommerce_save_product_variation( $variation_id, $i ) {

		// Make Aquos ID field required on saving
		if ( isset( $_POST['_aquos_id'][ $i ] ) && empty( $_POST['_aquos_id'][ $i ])) {
			WC_Admin_Meta_Boxes::add_error( __( 'Aquos ID is a required field', 'tmsm-aquos-spa-booking' ) );
		}

		if ( isset( $_POST['_aquos_id'][$i] ) ) :
			update_post_meta( $variation_id, '_aquos_id', sanitize_text_field( $_POST['_aquos_id'][$i] ) );
		endif;

	}

	/**
	 * Save Product Data
	 *
	 * @since 1.0.0
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	function woocommerce_process_product_save_options( $post_id ) {

		if ( isset( $_POST['_aquos_id'] ) ) :
			update_post_meta( $post_id, '_aquos_id', sanitize_text_field( $_POST['_aquos_id'] ) );
		endif;

		if ( isset( $_POST['_aquos_items_ids'] ) ) :
			update_post_meta( $post_id, '_aquos_items_ids', sanitize_textarea_field( $_POST['_aquos_items_ids'] ) );
		endif;

		if ( isset( $_POST['_bookable'] ) ) {
			update_post_meta( $post_id, '_bookable', sanitize_textarea_field( $_POST['_bookable'] )  );
		}
		else{
			delete_post_meta($post_id, '_bookable');
		}

	}

	/**
	 * Add Aquos ID Field to Product Variation
	 *
	 * @param int     $loop           Position in the loop.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation      Post data.
	 */
	function woocommerce_variation_options_pricing( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( array(
				'id' => '_aquos_id[' . $loop . ']',
				'wrapper_class' => 'form-row',
				'label' => __( 'Aquos Product ID (for appointments and purchase)', 'tmsm-aquos-spa-booking' ),
				'value' => get_post_meta( $variation->ID, '_aquos_id', true ),
				'custom_attributes' => ['required' => 'required'],
			)
		);
		woocommerce_wp_text_input( array(
				'id' => '_aquos_price[' . $loop . ']',
				'wrapper_class' => 'form-row',
				'label' => __( 'Aquos Product Price (for web service synchronization)', 'tmsm-aquos-spa-booking' ),
				'value' => get_post_meta( $variation->ID, '_aquos_price', true ),
				'custom_attributes' => ['disabled' => 'disabled']
			)
		);

	}

	/**
	 * Regenerate Aquos Prices
	 *
	 * @param bool $echo
	 */
	function regenerate_aquos_prices($echo = false){

		$output = '';

		global $wpdb;


		// Finding missings Aquos IDs
		$missing_aquos_ids = [];
		$products = wc_get_products(['numberposts' => -1, 'post_status' => 'publish']);
		foreach($products as $product){

			if ($product->get_type() == "variable") {
				foreach ($product->get_available_variations() as $variation) {

						$aquos_id = get_post_meta($variation['variation_id'], '_aquos_id', true);

						if(empty($aquos_id)){
							$missing_aquos_ids[$variation['variation_id']] = '<a href="'.get_edit_post_link($product->get_id()).'">'.$product->get_name(). '</a> / ' . ' - '.$variation['sku']. ' variation ID '. $product->get_id().' / aquos_id missing';
						}

				}
			}
			else{
				$aquos_id = get_post_meta($product->get_id(), '_aquos_id', true);
				if(empty($aquos_id)){
					$missing_aquos_ids[$product->get_id()] = '<a href="'.get_edit_post_link($product->get_id()).'">'.$product->get_name(). '</a> / ' . ' product ID '.$product->get_id().' / aquos_id missing';
				}
			}

		}
		if(!empty($missing_aquos_ids)){
			$output .= __( 'This section finds if Aquos IDs are missing for the web service synchronization:', 'tmsm-aquos-spa-booking' );
			$output .= '<br>';
			$output .= implode('<br>', $missing_aquos_ids);
			$output .= '<br>';
			$output .= '<br>';
		}



		// Finding non matching prices
		$meta_key_aquos_price = '_aquos_price';
		$non_matching_prices = [];

		$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => $meta_key_aquos_price ] );

		$results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = '_aquos_id' AND meta_value != '' ");

		if(!empty($results)){
			$count_meta = count( $results );
			$errors_nb = 0;

			//echo sprintf(__( 'Parsing %d products', 'tmsm-aquos-spa-booking' ), $count_meta);

			foreach ( $results as $result ) {

				$non_matching_prices[$result->post_id] = '';
				$non_matching_prices[$result->post_id] .= 'product id '.$result->post_id . ' / ';

				$aquos_price = [];
				$aquos_ids   = explode( '+', $result->meta_value );
				foreach ( $aquos_ids as $aquos_id ) {
					$price = self::get_product_price_from_aquos_or_product_id( $aquos_id, $result->post_id );
					$aquos_price[] = $price;
					if($price == 0){
						$non_matching_prices[$result->post_id] .= sprintf(__( 'Price not found for product %s', 'tmsm-aquos-spa-booking' ), $result->post_id);
						$non_matching_prices[$result->post_id] .= ' / ';
					}
				}
				$result->aquos_price = join( '+', $aquos_price );
				$non_matching_prices[$result->post_id] .= 'detailed price '.$result->aquos_price . ' / ';

				// Checking if calculated price matches product price
				$product = wc_get_product($result->post_id);
				$product_price = 0;
				if(!empty($product)){
					$product_price = $product->get_price();
				}
				$calculated_price = 0;
				foreach($aquos_price as $item_price){
					if(!empty($item_price)){
						$calculated_price+=$item_price;
					}
				}
				$non_matching_prices[$result->post_id] .= 'calculated price '.$calculated_price . ' / ';

				if($calculated_price != $product_price){
					$non_matching_prices[$result->post_id] .= '<b>NON MATCHING PRICE with '.$product_price . '</b> / ';

					if(!empty($product)){
						$non_matching_prices[$result->post_id] .= 'product is '. $product->get_status() . ' / ';
						if($product->get_status() === 'draft'){
							unset($non_matching_prices[$result->post_id]);
						}
						else{
							$parent = wc_get_product($product->get_parent_id());
							if(!empty($parent)){
								$non_matching_prices[$result->post_id] .= 'variation is '.  $parent->get_status(). ' / ';
								$non_matching_prices[$result->post_id] = '<a href="'.get_edit_post_link($parent->get_id()).'">'.$parent->get_name(). '</a> / ' . $non_matching_prices[$result->post_id];
								if($parent->get_status() === 'draft'){
									unset($non_matching_prices[$result->post_id]);
								}
							}
						}

					}

					if(!empty($product) && ! $product->get_status() == 'published'){
						//echo 'product is draft '. $product->get_status() . ' / ';
					}
					if(!empty($product) && $product->is_type('variation') ){
						$parent = wc_get_product($product->get_parent_id());
						if(!empty($parent) && ! $parent->get_status() == 'published'){
							//echo 'variation is draft / ';
						}
					}

					$errors_nb ++;
				}
				else{
					unset($non_matching_prices[$result->post_id]);
				}

				if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
					//print_r( $result );
				}

				// Insert the values
				$wpdb->query($wpdb->prepare(" INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value ) VALUES ( %d, %s, %s ) ",
					$result->post_id,
					$meta_key_aquos_price,
					$result->aquos_price
				)
				);

			}


		}

		if(!empty($non_matching_prices)){
			$output .= __( 'This section lists the products/variations with a non-matching price for the web service synchronization:', 'tmsm-aquos-spa-booking' );
			$output .= '<br>';
			$output .= implode('<br>', $non_matching_prices);
			$output .= '<br>';
			$output .= '<br>';
		}

		// If any error occured, output is defined
		if(!empty($output)){
			if ( $echo ) {// print result
				echo $output;
			}
			else {// email result

				$blogname = esc_html( get_bloginfo( 'name' ) );
				$email    = stripslashes( get_option( 'admin_email' ) );
				$subject  = sprintf(__( '%s: TMSM Aquos Spa Booking regeneration error on %s', 'tmsm-aquos-spa-booking' ), $blogname, date('Y-m-d'));

				$message = $output;

				$headers = [
					'Auto-Submitted: auto-generated',
					'Content-Type: text/html',
					'Charset=UTF-8'
				];
				$email_sent = wp_mail( $email, $subject, $message, $headers );
				if (  TMSM_AQUOS_SPA_BOOKING_DEBUG && !$email_sent) {
					error_log('Error email sent');
				}
			}
		}
	}

	/**
	 * Get Price From Aquos or Product ID
	 *
	 * @param int $aquos_id
	 * @param int $product_id
	 *
	 * @return int
	 */
	function get_product_price_from_aquos_or_product_id(int $aquos_id, int $product_id){

		global $wpdb;

		$price = 0;

		if(is_multisite() && get_current_blog_id() === 6) { // Rennes
			switch ( $aquos_id ) {
				case 338: // Parcours Aquatonic
					$price = 20;
					break;
				case 191: // Modelage nuque et cuir chevelu
					$price = 18;
					break;
				case 538: // Modelage dos d'accueil
					$price = 18;
					break;
				case 427: // Une nuit Oceania
					$price = 80;
					break;
			}
		}
		if(is_multisite() && get_current_blog_id() === 8) { // Nantes
			switch ( $aquos_id ) {
				case 338: // Parcours Aquatonic
					$price = 23;
					break;
				case 191: // Modelage nuque et cuir chevelu
					$price = 18;
					break;
				case 538: // Modelage dos d'accueil
					$price = 18;
					break;
			}
		}
		if ( is_multisite() && get_current_blog_id() === 9 ) { // Paris
			switch ( $aquos_id ) {
				case 368: // Parcours Aquatonic
					$price = 30;
					break;
				case 470: // Accès Espace Bien-être en complément d'un soin
					$price = 30;
					break;
				case 495: // Balnéo privative en duo
					$price = 39;
					break;
				case 847: // Supplément French Manucure
					$price = 5;
					break;
			}
		}

		if( $price == 0 ){
			//echo 'findind product with aquos id '.$aquos_id.' / ';

			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $wpdb->postmeta WHERE meta_key = '_aquos_id' AND meta_value = '%s'", $aquos_id
				)
			);
			//echo "SELECT * FROM $wpdb->postmeta WHERE meta_key = '_aquos_price' AND meta_value = " .$aquos_id . ' / ';

			// Variable price procucts (like e-check)
			if ( count( $results ) > 4 ) {
				$product = wc_get_product( $product_id );
				$price   = $product->get_price();
				//echo 'product price variable '.$price . ' / ';
			}

			// Other cases
			else{

				$result = null;

				// Take only the first result
				if ( is_array( $results ) && count( $results ) > 0 ) {
					$result = $results[0];
				}

				//print_r($results);
				if(!empty($result)){
					//echo 'finding product with post_id '.$result->post_id . ' / ';
					$product = wc_get_product($result->post_id);

					if(!empty($product)){

						$price = $product->get_price();
						//echo 'product price '.$price . ' / ';

						/*$parent_id = $product->get_parent_id();
						if(!empty($parent_id)){
							echo 'found parent product '.$parent_id. ' / ';
							$parent_product = wc_get_product($parent_id);
							if(!empty($parent_product)){
								$price = $parent_product->get_price();
								echo 'parent price '.$price. ' / ';
							}
						}*/


					}
				}
			}




			/*$product = wc_get_product($product_id);

			if(!empty($product)){

				$price = $product->get_price();
				echo 'product price '.$price . ' / ';

				$parent_id = $product->get_parent_id();
				if(!empty($parent_id)){
					echo 'found parent product '.$parent_id. ' / ';
					$parent_product = wc_get_product($parent_id);
					if(!empty($parent_product)){
						$price = $parent_product->get_price();
						echo 'parent price '.$price. ' / ';
					}
				}


			}*/
		}
		else{
			//echo 'hard coded price '.$price . ' for '.$aquos_id. ' / ';
		}

		return $price;
	}


}
