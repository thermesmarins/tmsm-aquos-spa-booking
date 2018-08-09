<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://github.com/nicomollet/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/public
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquos_Spa_Booking_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Get locale
	 */
	private function get_locale() {
		return (function_exists('pll_current_language') ? pll_current_language() : substr(get_locale(),0, 2));
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-aquos-spa-booking-public.css', array(), $this->version, 'all' );

		wp_deregister_style('bootstrap-datepicker');
		wp_enqueue_style( 'bootstrap-datepicker', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css', array($this->plugin_name),null );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_deregister_script('bootstrap-datepicker');
		wp_enqueue_script( 'bootstrap-datepicker', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js', array( 'jquery', 'bootstrap' ), null, true );


		if ( $this->get_locale() !== 'en' ) {
			wp_deregister_script( 'bootstrap-datepicker-' . $this->get_locale() );
			wp_enqueue_script( 'bootstrap-datepicker-' . $this->get_locale(), 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.' . $this->get_locale() . '.min.js',
				array( 'jquery', 'bootstrap', 'bootstrap-datepicker' ), null, true );
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquos-spa-booking-public.js', array( 'jquery', 'wp-util', 'bootstrap-datepicker' ), $this->version, true );
		// Params
		$params = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'locale'   => $this->get_locale(),
			'security' => wp_create_nonce( 'security' ),
			'i18n'     => [
				'fromprice'          => _x( 'From', 'price', 'tmsm-aquos-spa-booking' ),
			],
			'options'  => [
			],
		];

		wp_localize_script( $this->plugin_name, 'tmsm_aquos_spa_booking_params', $params);
	}


	/**
	 * Register the shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'tmsm-aquos-spa-booking', array( $this, 'booking_page') );
	}


	/**
	 * Button Class
	 *
	 * @return string
	 */
	private static function button_class(){
		$theme = wp_get_theme();
		$buttonclass = '';
		if ( 'StormBringer' == $theme->get( 'Name' ) || 'stormbringer' == $theme->get( 'Template' ) ) {
			$buttonclass = 'btn btn-primary';
		}
		if ( 'OceanWP' == $theme->get( 'Name' ) || 'oceanwp' == $theme->get( 'Template' ) ) {
			$buttonclass = 'button';
		}
		return $buttonclass;
	}

	/**
	 * Booking Page Shortcode
	 *
	 * @since    1.0.0
	 */
	public function booking_page($atts) {
		$atts = shortcode_atts( array(
			'option' => '',
		), $atts, 'tmsm-aquos-spa-booking' );

		$output = '';



		$output = '
		<form id="tmsm-aquos-spa-booking-form">
		<div id="tmsm-aquos-spa-booking-categories-container">
		<h2>'. __( 'Pick your treatments category:', 'tmsm-aquos-spa-booking' ).'</h2>
		<ul id="tmsm-aquos-spa-booking-categories" class="list-group">
		</ul>
		</div>
		
		<div id="tmsm-aquos-spa-booking-products-container">
		<h2>'. __( 'Pick your treatment:', 'tmsm-aquos-spa-booking' ).'</h2>
		<div id="tmsm-aquos-spa-booking-products"></div>
		</div>
		<div id="tmsm-aquos-spa-booking-date-container">
		<h2>'. __( 'Pick your date:', 'tmsm-aquos-spa-booking' ).'</h2>
		<div id="tmsm-aquos-spa-booking-datepicker" class="panel panel-default">
		</div>
		</div>
		<div id="tmsm-aquos-spa-booking-times-container">
		<h2>'. __( 'Pick your time:', 'tmsm-aquos-spa-booking' ).'</h2>
		<p id="tmsm-aquos-spa-booking-date-display"></p>
		<div id="tmsm-aquos-spa-booking-times"></div>
		</div>
		<p id="tmsm-aquos-spa-booking-confirm-container"><a href="#" class="'.self::button_class().'" id="tmsm-aquos-spa-booking-confirm" style="display: none;">'. __( 'Confirm this booking', 'tmsm-aquos-spa-booking' ).'</a></p>
		<input type="hidden" name="language" value="'.$this->get_locale().'">
		<input type="hidden" id="tmsm-aquos-spa-booking-selected-productcategory" name="productcategory" value="">
		<input type="hidden" id="tmsm-aquos-spa-booking-selected-product" name="product" value="">
		<input type="hidden" id="tmsm-aquos-spa-booking-selected-date" name="date" value="">
		<input type="hidden" id="tmsm-aquos-spa-booking-selected-time" name="time" value="">
		'.wp_nonce_field( 'tmsm-aquos-spa-booking-nonce-action', 'tmsm-aquos-spa-booking-nonce', true, false ).'
		</form>';

		return $output;
	}


	/**
	 * Date Template
	 */
	public function time_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-time">
			<p class="tmsm-aquos-spa-booking-time-group"><a class="<?php echo self::button_class(); ?> tmsm-aquos-spa-booking-time" href="#" data-time="{{ data.hour }}">{{ data.hour_formatted }}</a> <a href="#" class="tmsm-aquos-spa-booking-time-change-label"><?php echo __( 'Change time', 'tmsm-aquos-spa-booking' ); ?></a></p>
		</script>
		<?php
	}


	/**
	 * Product Category Template
	 */
	public function product_category_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-product-category">
			<li class="list-group-item"><a class="tmsm-aquos-spa-booking-product-category" href="#" data-product-category="{{ data.term_id }}">{{ data.name }}</a></li>
		</script>
		<?php
	}

	/**
	 * Product Template
	 */
	public function product_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-product">
			<div class="media tmsm-aquos-spa-booking-product">
				<div class="media-left">
					<img class="media-object" src="{{ data.thumbnail }}" alt="{{ data.name }}">
				</div>
				<div class="media-body">
					<h4 class="media-heading">{{ data.name }}</h4>
					<h6 class="media-heading">{{{ data.price }}}</h6>
					<p><a href="{{ data.permalink }}"><?php echo __('Know more about this product', 'tmsm-aquos-spa-booking');?></a></p>
					<p><a href="#" class="<?php echo self::button_class(); ?> tmsm-aquos-spa-booking-product-select" data-product="{{ data.id }}">
							<span class="tmsm-aquos-spa-booking-product-select-label"><?php echo __('Book this treatment', 'tmsm-aquos-spa-booking');?></span>
							<span class="tmsm-aquos-spa-booking-product-selected-label"><?php echo __('Treatment selected', 'tmsm-aquos-spa-booking');?></span>
						</a></p>
					<p><a href="#" class="tmsm-aquos-spa-booking-product-change-label" ><?php echo __('Change treatment', 'tmsm-aquos-spa-booking');?></a></p>
				</div>
			</div>
		</script>
		<?php

	}


	/**
	 * Ajax For Product Categories
	 *
	 * @since    1.0.0
	 */
	public static function ajax_product_categories() {

		$security = sanitize_text_field( $_POST['security'] );

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('ajax_product_categories');
		}

		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquos-spa-booking-nonce-action' ) ) {
			$errors[] = __('Token security not valid', 'tmsm-aquos-spa-booking');
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Ajax security not OK');
			}
		}
		else{
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Ajax security OK');
			}

			check_ajax_referer( 'tmsm-aquos-spa-booking-nonce-action', 'security' );

			$product_categories = get_terms( 'product_cat', array(
				'hide_empty' => true,
				'parent' => 57,
				'orderby'    => 'parent',
			) );
			$jsondata['product_categories'] = $product_categories;
		}


		// Return a response
		if( ! empty($errors) ) {
			$jsondata['success'] = false;
			$jsondata['errors']  = $errors;
		}
		else {
			$jsondata['success'] = true;
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			//error_log('json data:');
			//error_log(print_r($jsondata, true));
		}

		wp_send_json($jsondata);
		wp_die();

	}

	/**
	 * Ajax For Products
	 *
	 * @since    1.0.0
	 */
	public static function ajax_products() {

		$security = sanitize_text_field( $_POST['security'] );
		$product_category_id = sanitize_text_field( $_POST['productcategory'] );

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('ajax_products');
		}

		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquos-spa-booking-nonce-action' ) || empty($product_category_id)) {
			$errors[] = __('Token security not valid', 'tmsm-aquos-spa-booking');
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Ajax security not OK');
			}
		}
		else{
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Ajax security OK');
			}

			check_ajax_referer( 'tmsm-aquos-spa-booking-nonce-action', 'security' );

			$product_category = get_term( $product_category_id, 'product_cat');
			$args = array(
				'category' => $product_category->slug,
				'return'  => 'ids',
				'limit' => -1,
			);
			$products_ids = wc_get_products( $args );
			$products = [];
			if(!empty($products_ids)){
				foreach($products_ids as $key => $product_id){
					$product = wc_get_product($product_id);

					$product_virtual_chepeast = null;

					if($product->is_virtual()){
						$product_virtual_chepeast = $product;
					}
					else{
						if($product->is_type( 'variable' ) ){
							foreach ( $product->get_available_variations() as $variation_data ) {
								if(empty($variation_data['variation_id'])){
									continue;
								}
								$variation = wc_get_product($variation_data['variation_id']);
								if(!$variation->get_virtual()){
									continue;
								}
								if($product->get_price() === $variation->get_price()){
									$product_virtual_chepeast = $variation;
								}
							}
						}
					}

					if(!empty($product_virtual_chepeast)){
						$products[$key] = [
							'id' => esc_js($product_virtual_chepeast->get_id()),
							'permalink' => esc_js($product->get_permalink()),
							'thumbnail' => get_the_post_thumbnail_url($product_id) ? get_the_post_thumbnail_url($product_id) : '',
							'price' => $product_virtual_chepeast->get_price_html(),
							'name' => esc_js($product_virtual_chepeast->get_name()),
						];
					}

				}
			}
			else{
				$errors[] = __('No product in this category', 'tmsm-aquos-spa-booking');
			}

			$jsondata['products'] = $products;
		}


		// Return a response
		if( ! empty($errors) ) {
			$jsondata['success'] = false;
			$jsondata['errors']  = $errors;
		}
		else {
			$jsondata['success'] = true;
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			//error_log('json data:');
			//error_log(print_r($jsondata, true));
		}

		wp_send_json($jsondata);
		wp_die();

	}

	/**
	 * Ajax For Times
	 *
	 * @since    1.0.0
	 */
	public static function ajax_times() {

		$security = sanitize_text_field( $_POST['security'] );
		$product_category_id = sanitize_text_field( $_POST['productcategory'] );
		$product_id = sanitize_text_field( $_POST['product'] );
		$date = sanitize_text_field( $_POST['date'] );

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('ajax_times');
		}

		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquos-spa-booking-nonce-action' ) || empty($product_category_id) || empty($product_id) || empty($date)) {
			$errors[] = __('Token security not valid', 'tmsm-aquos-spa-booking');
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Ajax security not OK');
			}
		}
		else{
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Ajax security OK');
			}

			check_ajax_referer( 'tmsm-aquos-spa-booking-nonce-action', 'security' );


			// @TODO connect to TMSM webservice
			$times[] = [ 'hour'=> 10];
			$times[] = [ 'hour'=> 11];
			$times[] = [ 'hour'=> 15];
			$times[] = [ 'hour'=> 17];
			$jsondata['times'] = $times;
		}


		// Return a response
		if( ! empty($errors) ) {
			$jsondata['success'] = false;
			$jsondata['errors']  = $errors;
		}
		else {
			$jsondata['success'] = true;
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			//error_log('json data:');
			//error_log(print_r($jsondata, true));
		}

		wp_send_json($jsondata);
		wp_die();
	}

	/**
	 * Ajax For Add To Cart
	 *
	 * @since    1.0.0
	 */
	public static function ajax_addtocart() {

		$security = sanitize_text_field( $_POST['security'] );
		$product_category_id = sanitize_text_field( $_POST['productcategory'] );
		$product_id = sanitize_text_field( $_POST['product'] );
		$time = sanitize_text_field( $_POST['time'] );
		$date = sanitize_text_field( $_POST['date'] );

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('ajax_times');
		}

		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquos-spa-booking-nonce-action' ) || empty($product_category_id) || empty($product_id) || empty($date) || empty($time)) {
			$errors[] = __('Token security not valid', 'tmsm-aquos-spa-booking');
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Ajax security not OK');
			}
		}
		else{
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Ajax security OK');
			}

			check_ajax_referer( 'tmsm-aquos-spa-booking-nonce-action', 'security' );

			// Add To Cart
			//$product_id = 10554 ;
			$datetime = new \DateTime($date . ' ' . $time . ':00:00');
			$timestamp = $datetime->getTimestamp();
			$date_formatted = date_i18n( get_option( 'date_format' ), $timestamp );
			$time_formatted = date_i18n( get_option( 'time_format' ), $timestamp );
			$appointment = sprintf(
				_x( '%s at %s', 'date+time', 'tmsm-aquos-spa-booking' ),
				$date_formatted,
				$time_formatted
			);
			$quantity = 1;
			$variation_id = 10554;
			$variation = array();
			$cart_item_data = [
				'appointment' => $appointment
			];

			$return = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data);

			$redirect = wc_get_cart_url();

		}

		// Return a response
		if( ! empty($errors) ) {
			$jsondata['success'] = false;
			$jsondata['errors']  = $errors;
		}
		else {
			$jsondata['success'] = true;
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			//error_log('json data:');
			//error_log(print_r($jsondata, true));
		}

		wp_send_json($jsondata);
		wp_die();
	}

	/**
	 * Add Appointment Data Date+Time When Adding To Cart
	 *
	 * @param array $cart_item_data
	 * @param int   $product_id
	 * @param int   $variation_id
	 *
	 * @return array
	 */
	public function woocommerce_add_cart_item_data_appointment( $cart_item_data, $product_id, $variation_id ){
		$appointment = sanitize_text_field(filter_input( INPUT_POST, 'appointment' ));

		if ( empty( $appointment ) ) {
			return $cart_item_data;
		}

		$cart_item_data['appointment'] = $appointment;

		return $cart_item_data;
	}

	/**
	 * Display Appointment Data Date+Time When Adding To Cart
	 *
	 * @param array $item_data
	 * @param array $cart_item
	 *
	 * @return array
	 */
	function woocommerce_get_item_data_appointment( $item_data, $cart_item ) {

		//error_log(print_r($item_data, true));
		//error_log(print_r($cart_item, true));
		if ( empty( $cart_item['appointment'] ) ) {
			return $item_data;
		}

		$item_data[] = array(
			'key'     => __( 'Appointment', 'tmsm-aquos-spa-booking' ),
			'value'   => wc_clean( $cart_item['appointment'] ),
			'display' => '',
		);

		return $item_data;
	}


	/**
	 * Update Order Item Meta With Appointment Data
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order_Item_Product $item
	 * @param string                $cart_item_key
	 * @param array                 $values
	 * @param WC_Order              $order
	 */
	public function woocommerce_checkout_create_order_line_item_appointment( $item, $cart_item_key, $values, $order ) {

		$variation_id = isset( $values['variation_id'] ) && ! empty( $values['variation_id'] ) ? $values['variation_id'] : $values['product_id'];

		$product = $item->get_product();

		if ( $product ) {
			$variation_id = $product->get_id();
		}

		if ( ! empty( $values['appointment'] ) ) {
			$item->add_meta_data( '_appointment', $values['appointment'], true );
		}

	}

}
