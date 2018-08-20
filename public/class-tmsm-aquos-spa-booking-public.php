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

		$startdate = new \DateTime();
		$startdate->modify('+1 day');
		$enddate = new \DateTime();
		$enddate->modify('+'.get_option( 'tmsm_aquos_spa_booking_daysrange', 30 ). ' days');

		// Params
		$params = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'locale'   => $this->get_locale(),
			'security' => wp_create_nonce( 'security' ),
			'i18n'     => [
				'fromprice'          => _x( 'From', 'price', 'tmsm-aquos-spa-booking' ),
			],
			'options'  => [
				'daysrange' => esc_js(get_option( 'tmsm_aquos_spa_booking_daysrange', 30 )),
				'enddate' => $enddate->format('Y-m-d'),
				'startdate' => $startdate->format('Y-m-d'),
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
		</form>
		<p>'.esc_html(get_option( 'tmsm_aquos_spa_booking_cancellationpolicy', '' )).'</p>
		';

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

			// Get "product_cat" Terms With Parent as an Option
			$settings_maincategory  = get_option( 'tmsm_aquos_spa_booking_productcat', 0 );
			$product_categories = get_terms( 'product_cat', [
				'hide_empty' => true,
				'parent' => !empty($settings_maincategory) ? $settings_maincategory: 0,
				'orderby'    => 'parent',
			]);

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

					$product_cheapest = null;

					$aquos_id = get_post_meta( $product_id, '_aquos_id', true);
					if(empty($aquos_id)){
						continue;
					}

					if(!$product->is_virtual() && !$product->is_type( 'variable' )){
						$product_cheapest = $product;
					}
					else{
						if($product->is_type( 'variable' ) ){
							foreach ( $product->get_available_variations() as $variation_data ) {

								if(empty($variation_data['variation_id'])){
									continue;
								}
								$variation = wc_get_product($variation_data['variation_id']);
								if($variation->get_virtual()){
									continue;
								}
								if($product->get_price() === $variation->get_price()){
									$product_cheapest = $variation;
								}
							}
						}
					}

					if ( ! empty( $product_cheapest ) ) {
						$products[$key] = [
							'id' => esc_js($product_cheapest->get_id()),
							'permalink' => esc_js($product->get_permalink()),
							'thumbnail' => get_the_post_thumbnail_url($product_id) ? get_the_post_thumbnail_url($product_id) : '',
							'price' => $product_cheapest->get_price_html(),
							'name' => esc_js($product_cheapest->get_name()),
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

		$product = wc_get_product($product_id);

		if($product->is_type('variation')){
			$product_id = $product->get_parent_id();
		}

		$aquos_id = get_post_meta($product_id, '_aquos_id', true);

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('ajax_times');
		}

		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquos-spa-booking-nonce-action' ) || empty($product_category_id) || empty($product_id) || empty($date) || empty($aquos_id) ) {
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

			// Call web service
			$settings_webserviceurl = get_option( 'tmsm_aquos_spa_booking_webserviceurl' );
			if(!empty($settings_webserviceurl)){

				error_log('url before:'.$settings_webserviceurl);

				$patterns = [
					'{date}',
					'{product_id}',
					'{site_id}',
					'{site_name}'
				];
				$replacements = [
					esc_html( $date ),
					esc_html( $aquos_id ),
					( is_multisite() ? get_current_blog_id() : "aaa" ),
					esc_html( get_bloginfo( 'name' ) ),
				];

				// Replace keywords in url
				$settings_webserviceurl = preg_replace($patterns, $replacements, $settings_webserviceurl);
				error_log( 'url after:' . $settings_webserviceurl );

				// Connect with cURL
				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_URL, $settings_webserviceurl );
				$result = curl_exec( $ch );
				curl_close( $ch );

				// @TODO analyse response
				error_log(var_export(json_decode($result, true), true));
			}

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
			$variation_id = $product_id;
			$variation = array();
			$cart_item_data = [
				'appointment' => $appointment,
				'timestamp_added' => time(),
				//'price' => 12 // if I want to force a price in the cart
			];

			$return = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data);

			$redirect = wc_get_cart_url();
			$jsondata['redirect'] = $redirect;

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

		/*$product = $item->get_product();

		if ( $product ) {
			$variation_id = $product->get_id();
		}*/

		if ( ! empty( $values['appointment'] ) ) {
			$item->add_meta_data( '_appointment', $values['appointment'], true );
		}

	}

	/**
	 * Displays hidden delivery date for order item in order view (frontend)
	 * /my-account/view-order/$order_id/
	 * /checkout/order-received/$order_id/
	 *
	 * @since 1.0.0
	 *
	 * @param  string        $html
	 * @param  WC_Order_Item $item
	 * @param  array         $args
	 *
	 * @return string
	 */
	public function woocommerce_display_item_meta_appointment( $html, $item, $args ) {

		$strings = [];

		if ( !empty($item['_appointment'])) {
			$strings[]           = '<strong class="wc-item-meta-label">' . __( 'Appointment:', 'tmsm-aquos-spa-booking' ) . '</strong> '.  esc_html($item['_appointment']) ;
		}

		if ( $strings != [] ) {
			$html .= $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
		}

		return $html;
	}

	/**
	 * Update Price in Cart
	 *
	 * @param WC_Cart $cart
	 */
	public function woocommerce_before_calculate_totals_appointment($cart){
		foreach ( $cart->cart_contents as $key => $value ) {
			//$value['data']->set_price($value['price']); // If I want to force a price in the cart
			if(!empty($value['appointment'])){
				$value['data']->set_virtual(true);
			}
		}
	}

	/**
	 * Disable Other Payments Gateways if Cas On Delivery is Prefered Method
	 *
	 * @param $available_gateways
	 *
	 * @return mixed
	 */
	function woocommerce_available_payment_gateways_cashondelivery( $available_gateways ) {
		global $woocommerce;

		$settings_acceptcashondelivery = get_option( 'tmsm_aquos_spa_booking_acceptcashondelivery', 'yes' );
		$settings_acceptonlinepayment = get_option( 'tmsm_aquos_spa_booking_acceptonlinepayment', 'no' );

		// Check cart content: if all products are appointments
		$all_appointments = true;
		foreach ( $woocommerce->cart->cart_contents as $key => $values ) {
			if(empty($values['appointment'])){
				$all_appointments = false;
				break;
			}
		}

		// All products are appointments, allow accepted methods
		if($all_appointments === true){

			if($settings_acceptcashondelivery === 'no'){
				unset($available_gateways['cod']);
			}
			if($settings_acceptonlinepayment === 'no' && $settings_acceptcashondelivery === 'yes'){
				if(!empty($available_gateways)){
					foreach ($available_gateways as $available_gateway_key => $available_gateway){
						if($available_gateway_key !== 'cod'){
							unset($available_gateways[$available_gateway_key]);
						}
					}
				}
			}
		}
		// If at least one product is not an appointment, then remove cod
		else{
			unset($available_gateways['cod']);
		}

		return $available_gateways;
	}


	/**
	 * If Order has at least one appointment
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	private function order_has_appointment($order){

		$has_appointment = false;

		if ( ! empty( $order ) ) {


			foreach ( $order->get_items() as $order_item_id => $order_item_data) {

				// Has appointment
				if(!empty($order_item_data['_appointment'])){
					$has_appointment = true;
				}

			}
		}
		return $has_appointment;
	}


	/**
	 * @param $thankyou
	 * @param $order WC_Order
	 *
	 * @return string
	 */
	function woocommerce_thankyou_order_received_text_appointment( $thankyou, $order ) {

		if ( self::order_has_appointment( $order ) === true ) {
			$message = get_option( 'tmsm_aquos_spa_booking_thankyou', false );

			if ( ! empty( $message ) ) {
				$thankyou .= '<br><br>' . esc_html( $message );
			}
		}

		return $thankyou;

	}

	/**
	 * Generates Order structured data.
	 *
	 * Hooked into `woocommerce_email_order_details` action hook.
	 *
	 * @param WC_Order $order         Order data.
	 * @param bool     $sent_to_admin Send to admin (default: false).
	 * @param bool     $plain_text    Plain text email (default: false).
	 * @param string   $email         Email address.
	 */
	public function woocommerce_email_before_order_table_appointment($order, $sent_to_admin, $plain_text, $email){

		if ( self::order_has_appointment( $order ) === true && $sent_to_admin === false ) {
			$message = get_option( 'tmsm_aquos_spa_booking_orderemail', false );

			if ( ! empty( $message ) ) {
				echo '<p>' . esc_html( $message ) . '</p>';
			}
		}

	}

	/**
	 * Remove Appointments that are expired, too hold = 2 hours
	 */
	public function woocommerce_check_cart_items_expire(){
		if( is_cart() || is_checkout() ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				if(!empty($cart_item['appointment']) && !empty($cart_item['timestamp_added'])){
					$_product = $cart_item['data'];
					if( time() > ( $cart_item['timestamp_added'] + 3600 * 2)){
						WC()->cart->remove_cart_item( $cart_item_key );
						wc_add_notice( sprintf( __( 'The product %s has been removed from cart since it has expired. Please try to book it again.', 'woocommerce' ), $_product->get_name() ), 'notice' );
					}
				}

			}
		}
	}

	/**
	 * Force orders be marked as "on hold" instead of "processing"
	 *
	 * @param $status
	 *
	 * @return string
	 */
	public function woocommerce_cod_process_payment_order_status($status){
		return 'on-hold';
	}
}
