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
	 *
	 * @throws Exception
	 */
	public function enqueue_scripts() {

		wp_deregister_script('bootstrap-datepicker');
		wp_enqueue_script( 'bootstrap-datepicker', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js', array( 'jquery', 'bootstrap' ), null, true );

		if ( $this->get_locale() !== 'en' ) {
			wp_deregister_script( 'bootstrap-datepicker-' . $this->get_locale() );
			wp_enqueue_script( 'bootstrap-datepicker-' . $this->get_locale(), 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.' . $this->get_locale() . '.min.js',
				array( 'jquery', 'bootstrap', 'bootstrap-datepicker' ), null, true );
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquos-spa-booking-public.js', array( 'jquery', 'wp-util', 'bootstrap-datepicker', 'wp-api' ), $this->version, true );

		$startdate = new \DateTime();
		$startdate->modify('+'.get_option( 'tmsm_aquos_spa_booking_daysrangefrom', 1 ). ' days');
		$enddate = new \DateTime();
		$enddate->modify('+'.get_option( 'tmsm_aquos_spa_booking_daysrangeto', 60 ). ' days');

		// Params
		$params = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'locale'   => $this->get_locale(),
			'security' => wp_create_nonce( 'tmsm-aquos-spa-booking-nonce-action' ),
			'i18n'     => [
				'fromprice'          => _x( 'From', 'price', 'tmsm-aquos-spa-booking' ),
				'selectcategory'          => __( 'Select a category', 'tmsm-aquos-spa-booking' ),
				'selectproduct'          => __( 'Select a product', 'tmsm-aquos-spa-booking' ),
				'loading'          => __( 'Loading', 'tmsm-aquos-spa-booking' ),
			],
			'options'  => [
				'daysrangefrom' => esc_js(get_option( 'tmsm_aquos_spa_booking_daysrangefrom', 1 )),
				'daysrangeto' => esc_js(get_option( 'tmsm_aquos_spa_booking_daysrangeto', 60 )),
				'enddate' => $enddate->format('Y-m-d'),
				'startdate' => $startdate->format('Y-m-d'),
			],
		];

		wp_localize_script( $this->plugin_name, 'tmsm_aquos_spa_booking_params', $params);


		// Add initial data to CronPixie JS object so it can be rendered without fetch.
		// Also add translatable strings for JS as well as reference settings.
		$data = array(
			'strings'      => array(
				'no_selection'    => __( 'No selection', 'tmsm-aquos-spa-booking' ),
				'no_events'    => _x( '(none)', 'no event to show', 'tmsm-aquos-spa-booking' ),
				'due'          => _x( 'due', 'label for when cron event date', 'tmsm-aquos-spa-booking' ),
				'now'          => _x( 'now', 'cron event is due now', 'tmsm-aquos-spa-booking' ),
				'passed'       => _x( 'passed', 'cron event is over due', 'tmsm-aquos-spa-booking' ),
				'weeks_abrv'   => _x( 'w', 'displayed in interval', 'tmsm-aquos-spa-booking' ),
				'days_abrv'    => _x( 'd', 'displayed in interval', 'tmsm-aquos-spa-booking' ),
				'hours_abrv'   => _x( 'h', 'displayed in interval', 'tmsm-aquos-spa-booking' ),
				'minutes_abrv' => _x( 'm', 'displayed in interval', 'tmsm-aquos-spa-booking' ),
				'seconds_abrv' => _x( 's', 'displayed in interval', 'tmsm-aquos-spa-booking' ),
				'run_now'      => _x( 'Run event now.', 'Title for run now icon', 'tmsm-aquos-spa-booking' ),
			),
			'calendar'  => [
				'daysrangefrom' => esc_js(get_option( 'tmsm_aquos_spa_booking_daysrangefrom', 1 )),
				'daysrangeto' => esc_js(get_option( 'tmsm_aquos_spa_booking_daysrangeto', 60 )),
				'enddate' => $enddate->format('Y-m-d'),
				'startdate' => $startdate->format('Y-m-d'),
			],
			'role'   => current_user_can('edit_posts'),
			'locale'   => $this->get_locale(),
			'ajaxurl'        => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'tmsm-aquos-spa-booking-nonce-action' ),
			'timer_period' => 5, // How often should display be updated, in seconds.
			'data'         => array(
				'havevoucher' => [
					'yes' => [
						'name' => __( 'I have a voucher', 'tmsm-aquos-spa-booking' ),
						'slug' => 'yes',
						'value' => 1,
					],
					'no' => [
						'name' => __( 'I don\'t have any voucher', 'tmsm-aquos-spa-booking' ),
						'slug' => 'no',
						'value' => 0,
					],
				],
				//'schedules' => $this->_get_schedules(),
				'productcategories' => $this->_get_product_categories(),
				'products' => $this->_get_products(),
				'productattributes' => array(),
				'productvariations' => array(),
				'times' => array(),
			),
		);
		wp_localize_script( $this->plugin_name, 'TmsmAquosSpaBookingApp', $data );
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
	 * Button Class Default
	 *
	 * @return string
	 */
	private static function button_class_default(){
		$theme = wp_get_theme();
		$buttonclass = '';
		if ( 'StormBringer' == $theme->get( 'Name' ) || 'stormbringer' == $theme->get( 'Template' ) ) {
			$buttonclass = 'btn btn-default';
		}
		if ( 'OceanWP' == $theme->get( 'Name' ) || 'oceanwp' == $theme->get( 'Template' ) ) {
			$buttonclass = 'button';
		}
		return $buttonclass;
	}

	/**
	 * Button Class Primary
	 *
	 * @return string
	 */
	private static function button_class_primary(){
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
	 * Alert Class Error
	 *
	 * @return string
	 */
	private static function alert_class_error(){
		$theme = wp_get_theme();
		$buttonclass = '';
		if ( 'StormBringer' == $theme->get( 'Name' ) || 'stormbringer' == $theme->get( 'Template' ) ) {
			$buttonclass = 'alert alert-danger';
		}
		if ( 'OceanWP' == $theme->get( 'Name' ) || 'oceanwp' == $theme->get( 'Template' ) ) {
			$buttonclass = 'alert';
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


		// Check if cart has products other than appointments
		$cart_has_products = false;
		if(!empty(WC()->cart->get_cart_contents())){
			foreach(WC()->cart->get_cart_contents() as $cart_item){
				//print_r($cart_item);
				if(empty($cart_item['appointment'])){
					$cart_has_products = true;
				}
			}
		}
		$output = '';
		if($cart_has_products){
			$output = '<p id="tmsm-aquos-spa-booking-emptycartfirst" class="'.self::alert_class_error().'" >'.sprintf(__( 'Your cart contains products other than appointments, please <a href="%s">empty your cart or complete the order</a> before booking an appointment.', 'tmsm-aquos-spa-booking' ), wc_get_cart_url()).'</p>';
		}
		else {

			$output = '
			<form id="tmsm-aquos-spa-booking-form">
			
			<div id="tmsm-aquos-spa-booking-voucher-container">
			<div id="tmsm-aquos-spa-booking-voucher-inner">
			<h3>' . __( 'Do you have a voucher?', 'tmsm-aquos-spa-booking' ) . '</h3>
			<ul id="tmsm-aquos-spa-booking-voucher-list" class="list-unstyled"></ul>
			<!--
			<label class="radio-inline">
			  <input type="radio" name="tmsm-aquos-spa-booking-voucher" id="tmsm-aquos-spa-booking-voucheryes" value="1" autocomplete="off"> ' . __( 'I have a voucher', 'tmsm-aquos-spa-booking' ) . '
			</label>
			<label class="radio-inline">
			  <input type="radio" name="tmsm-aquos-spa-booking-voucher" id="tmsm-aquos-spa-booking-voucherno" value="0" autocomplete="off"> ' . __( 'I don\'t have any voucher', 'tmsm-aquos-spa-booking' ) . '
			</label>
			-->
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-categories-container" >
			<div id="tmsm-aquos-spa-booking-categories-inner">
			<h3>' . __( 'Pick your treatments category:', 'tmsm-aquos-spa-booking' ) . '</h3>
			<select id="tmsm-aquos-spa-booking-categories-select" data-mobile="true" title="' . esc_attr__( 'No selection', 'tmsm-aquos-spa-booking' ) . '">
			</select>
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-products-container" >
			<div id="tmsm-aquos-spa-booking-products-inner">
			<h3>' . __( 'Pick your treatment:', 'tmsm-aquos-spa-booking' ) . '</h3>
			<p id="tmsm-aquos-spa-booking-products-loading">' . __( 'Loading', 'tmsm-aquos-spa-booking' ) . '</p>
			<select id="tmsm-aquos-spa-booking-products-select" data-mobile="true" title="' . esc_attr__( 'No selection', 'tmsm-aquos-spa-booking' ) . '"></select>
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-attributes-container" >
			<div id="tmsm-aquos-spa-booking-attributes-inner">
			<h3>' . __( 'Pick your variation:', 'tmsm-aquos-spa-booking' ) . '</h3>
			
			<p id="tmsm-aquos-spa-booking-attributes-loading">' . __( 'Loading', 'tmsm-aquos-spa-booking' ) . '</p>
			<ul id="tmsm-aquos-spa-booking-attributes-list" class="list-unstyled"></ul>
			<p class="tmsm-aquos-spa-booking-attributes-reset-confirm">
			<a href="#" id="tmsm-aquos-spa-booking-attributes-cancel" class="'.self::button_class_default().'">' . __( 'Reset your options', 'tmsm-aquos-spa-booking' ) . '</a>
			<a href="#" id="tmsm-aquos-spa-booking-attributes-confirm" class="'.self::button_class_primary().'">' . __( 'Confirm your options', 'tmsm-aquos-spa-booking' ) . '</a>
			</p>
			<select id="tmsm-aquos-spa-booking-variations-select" data-mobile="true" title="' . esc_attr__( 'No selection', 'tmsm-aquos-spa-booking' ) . '"></select>
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-date-container" >
			<div id="tmsm-aquos-spa-booking-date-inner">
			<h3>' . __( 'Pick your date:', 'tmsm-aquos-spa-booking' ) . '</h3>
			<div id="tmsm-aquos-spa-booking-datepicker" class="panel panel-default">
			</div>
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-times-container" >
			<div id="tmsm-aquos-spa-booking-times-inner">
			<h3>' . __( 'Pick your time:', 'tmsm-aquos-spa-booking' ) . '</h3>
			<p id="tmsm-aquos-spa-booking-date-display"></p>
			<p id="tmsm-aquos-spa-booking-times-loading">' . __( 'Loading', 'tmsm-aquos-spa-booking' ) . '</p>
			<p id="tmsm-aquos-spa-booking-times-error" style="display: none">' . __( 'No time results for this date', 'tmsm-aquos-spa-booking' ) . '</p>
			<ul id="tmsm-aquos-spa-booking-times-list" class="list-unstyled"></ul>
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-confirm-container">
			
			<p id="tmsm-aquos-spa-booking-confirm-error"></p>
			<button class="' . self::button_class_default() . '" id="tmsm-aquos-spa-booking-cancel">' . __( 'Cancel', 'tmsm-aquos-spa-booking' ) . '</button>		
			<button class="' . self::button_class_primary() . '" id="tmsm-aquos-spa-booking-confirm">'
			          . __( 'Add this appointment', 'tmsm-aquos-spa-booking' ) . '</button>
			</div>
			</form>
			<p id="tmsm-aquos-spa-booking-cancellationpolicy" style="display: none">' . esc_html( get_option( 'tmsm_aquos_spa_booking_cancellationpolicy',
					'' ) ) . '</p>
			';

		}
		return $output;
	}

	/**
	 * Have Voucher Template
	 */
	public function havevoucher_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-havevoucher">
			<label class="radio-inline">
				<input type="radio" name="tmsm-aquos-spa-booking-voucher" id="tmsm-aquos-spa-booking-voucher{{ data.slug}}" value="{{ data.value}}" autocomplete="off"> {{ data.name}}
			</label>
		</script>
		<?php
	}


	/**
	 * Date Template
	 */
	public function time_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-time">
			<a class="tmsm-aquos-spa-booking-time-button <?php echo self::button_class_primary(); ?> tmsm-aquos-spa-booking-time" href="#" data-hour="{{ data.hour }}" data-minutes="{{ data.minutes }}" data-hourminutes="{{ data.hourminutes }}" data-priority="{{ data.priority }}">{{ data.hourminutes }} <# if ( TmsmAquosSpaBookingApp.role == "1" && data.priority == 1) { #> *<# } #></a> <a href="#" class="tmsm-aquos-spa-booking-time-change-label"><?php echo __( 'Change time', 'tmsm-aquos-spa-booking' ); ?></a>
		</script>
		<?php
	}

	/**
	 * Product Category Template
	 */
	public function product_category_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-product-category">
			<# if (data.parent !== <?php echo esc_html(get_option( 'tmsm_aquos_spa_booking_productcat', 0 )) ?>) { #>
			-&nbsp;&nbsp;
			<# } #>
			{{ data.name }} ({{ data.count }})
		</script>
		<?php
	}

	/**
	 * Product Template
	 */
	public function product_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-product">
				{{ data.name }} <# if ( data.is_voucher == '0') { #> — {{ data.price }} <# } #>

		</script>
		<?php
	}

	/**
	 * Product Variation Template
	 */
	public function product_variation_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-variation">
			{{ data.name }} <# if ( data.is_voucher == '0') { #> — {{ data.price }} <# } #>
		</script>
		<?php
	}

	/**
	 * Product Variation Template
	 */
	public function product_attribute_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-product-attribute">
			<span style="display: none;">{{ data.label }}</span>
			<# _.each( data.terms, function(term, index) { #>

			<label class="radio-inline" <# if ( ( term.name.indexOf('Sans') >= 0 )) { #> style="display:none"<# } #>>
			<input class="tmsm-aquos-spa-booking-term <# if ( ( term.name.indexOf('Sans') >= 0 )) { #> checked-default<# } #>" type="radio" id="{{ data.slug }}_v_{{ term.slug }}{{ data.productid }}" name="attribute_{{ data.slug }}" value="{{ term.slug }}" <# if ( ( term.name.indexOf('Sans') >= 0 )) { #> checked="checked"<# } #>>{{term.name}}
			</label>
			<# }) #>

		</script>
		<?php
	}

	/**
	 * Product Variation Template
	 */
	/*public function cronpixie_template(){
		?>

		<script type="text/template" id="cron-pixie-schedule-item-tmpl">
			<span class="cron-pixie-schedule-display"><%= display %></span>
			<ul class="cron-pixie-events"></ul>
		</script>

		<!-- Event Item template -->
		<script type="text/template" id="cron-pixie-event-item-tmpl">
			<% if ( undefined == hook ) { %>
			<span class="cron-pixie-event-empty"><%= TmsmAquosSpaBookingApp.strings.no_events %></span>
			<% } else { %>
			<span class="cron-pixie-event-run dashicons dashicons-controls-forward" title="<%- TmsmAquosSpaBookingApp.strings.run_now %>"></span>
			<span class="cron-pixie-event-hook"><%= hook %></span>
			<div class="cron-pixie-event-timestamp dashicons-before dashicons-clock">
				<span class="cron-pixie-event-due"><%- TmsmAquosSpaBookingApp.strings.due %>:&nbsp;<%= new Date( timestamp * 1000 ).toLocaleString() %></span>
				&nbsp;
			</div>
			<% } %>
		</script>

		<!-- Main content -->
		<div id="cron-pixie-main">
			<h3>Schedules</h3>
			<ul class="cron-pixie-schedules"></ul>
		</div>
		<?php
	}*/

	


	/**
	 * Ajax For Product Categories
	 *
	 * @since    1.0.0
	 */
	public function ajax_product_categories() {

		$this->_ajax_checksecurity();
		$this->_ajax_return( $this->_get_product_categories() );

	}

	/**
	 * Ajax For Products Attributes
	 *
	 * @since    1.0.0
	 */
	public function ajax_product_attributes() {

		$this->_ajax_checksecurity();
		$this->_ajax_return( $this->_get_product_attributes() );

	}

	/**
	 * Ajax check nonce security
	 */
	private function _ajax_checksecurity(){
		error_log('_ajax_checksecurity');

		error_log(print_r($_REQUEST, true));
		$security = sanitize_text_field( $_REQUEST['nonce'] );

		error_log('security: '.$security);
		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data
		
		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquos-spa-booking-nonce-action' ) ) {
			$errors[] = __('Token security is not valid', 'tmsm-aquos-spa-booking');
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Token security is not valid');
			}
		}
		else {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Token security is valid' );
			}
		}
		if(check_ajax_referer( 'tmsm-aquos-spa-booking-nonce-action', 'nonce' ) === false){
			$errors[] = __('Ajax referer is not valid', 'tmsm-aquos-spa-booking');
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Ajax referer is not valid');
			}
		}
		else{
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Ajax referer is valid' );
			}
		}

		if(!empty($errors)){
			wp_send_json($jsondata);
			wp_die();
		}

	}


	/**
	 * Ajax For Products
	 *
	 * @since    1.0.0
	 */
	public function ajax_products() {

		$this->_ajax_checksecurity();
		$this->_ajax_return( $this->_get_products() );

		/*
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
				'orderby' => 'name',
			);
			$products_ids = wc_get_products( $args );
			$products = [];
			if(!empty($products_ids)){
				foreach($products_ids as $key => $product_id){
					$product = wc_get_product($product_id);

					$products[$product->get_id()] = [
						'id' => esc_js($product->get_id()),
						'permalink' => esc_js($product->get_permalink()),
						'thumbnail' => get_the_post_thumbnail_url($product_id) ? get_the_post_thumbnail_url($product_id) : '',
						'price' => html_entity_decode(wp_strip_all_tags($product->get_price_html())),
						'sku' => esc_js($product->get_sku()),
						'name' => esc_js($product->get_name()),
						'variable' => esc_js($product->is_type( 'variable' )),
					];





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
		wp_die();*/

	}

	/**
	 * Ajax For Products Variations
	 *
	 * @since    1.0.0
	 */
	public function ajax_product_variations() {
		$this->_ajax_checksecurity();
		$this->_ajax_return( $this->_get_product_variations() );

	}

	/**
	 * Ajax For Times
	 *
	 * @since    1.0.0
	 */
	public function ajax_times() {
		$this->_ajax_checksecurity();
		$this->_ajax_return( $this->_get_times() );
	}


	/**
	 * Ajax For Add To Cart
	 *
	 * @since    1.0.0
	 *
	 * @throws Exception
	 */
	public static function ajax_addtocart() {

		error_log('ajax_addtocart');

		$selecteddata_array = isset( $_POST['selecteddata'] ) ? $_POST['selecteddata'] : array();

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		$nonce = sanitize_text_field( $_POST['nonce'] );
		$product_category_id = sanitize_text_field( $selecteddata_array['productcategory'] );
		$is_voucher = sanitize_text_field( $selecteddata_array['is_voucher'] );
		$product_id = sanitize_text_field( $selecteddata_array['product'] );
		$productvariation_id = sanitize_text_field( $selecteddata_array['productvariation'] );
		$date = sanitize_text_field( $selecteddata_array['date'] );
		$hourminutes = sanitize_text_field( $selecteddata_array['hourminutes'] );

		$product = wc_get_product($product_id);
		if(!empty($productvariation_id)){
			$productvariation = wc_get_product($productvariation_id);
			$product = $productvariation;
			$product_id = $productvariation_id;
		}

		// Product existance
		if ( empty( $product ) ) {
			$errors[] = __('Product not found', 'tmsm-aquos-spa-booking');
		}
		else{
			$aquos_id = sanitize_text_field( $product->get_meta('_aquos_id', true ));


			// Cart Item Data
			$datetime = new \DateTime($date . ' ' . $hourminutes.':00' );
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
				'has_voucher' => $is_voucher,
				'appointment' => $appointment,
				'appointment_date' => $date,
				'appointment_time' => $hourminutes,
				'aquos_id' => $aquos_id,
				'timestamp_added' => time(),
				//'price' => 12 // if I want to force a price in the cart
			];

			$return = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data);

			//$redirect = wc_get_cart_url();
			$redirect = wc_get_checkout_url();
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
			error_log('json data:');
			error_log(print_r($jsondata, true));
		}

		if($jsondata['success']){
			wp_send_json_success($jsondata);
		}
		else{
			wp_send_json_error($jsondata);
		}
	}

	/**
	 * Sort Times By Priority
	 *
	 * @param array $times
	 *
	 * @return array
	 */
	private function sort_times_by_priority($times) {

		uasort($times, array($this, 'compare_times_by_priority'));
		return $times;
	}

	/**
	 * Sort Times By Hour+Minutes
	 *
	 * @param array $times
	 *
	 * @return array
	 */
	private function sort_times_by_hourminutes($times) {

		uasort($times, array($this, 'compare_times_by_hourminutes'));
		return $times;
	}

	/**
	 * Compare Times By Priority
	 *
	 * @param $times
	 *
	 * @return mixed
	 */
	private function compare_times_by_priority($a, $b) {
		return $a['priority'] < $b['priority'] ;
	}

	/**
	 * Compare Times By Hour+Minutes
	 *
	 * @param $times
	 *
	 * @return mixed
	 */
	private function compare_times_by_hourminutes($a, $b) {
		return $a['hourminutes'] > $b['hourminutes'] ;
	}

	/**
	 * Select Times
	 *
	 * @param $times
	 *
	 * @return mixed
	 */
	private function select_times($times) {

		$count_priorities = 0;
		$count_notpriorities = 0;
		$not_priorities = [];
		$priorities = [];
		$selected_times = [];
		if(count($times)>0){
			foreach ($times as $time){
				if(!empty($time) && $time['priority'] == 1){
					$count_priorities++;
					$priorities[] = $time;
				}
				else{
					$count_notpriorities++;
					$not_priorities[] = $time;
				}
			}

			$diff_priorities = 4 - $count_priorities;
			$selected_times = $priorities;


			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('$diff_priorities: '.$diff_priorities);
				error_log('$count_priorities: '.$count_priorities);
				error_log('$count_notpriorities: '.$count_notpriorities);
				error_log('$priorities');
				error_log(print_r($priorities, true));
				error_log('$not_priorities');
				error_log(print_r($not_priorities, true));
			}

			// Complete with non priorities
			if($diff_priorities > 0){
				for ($cpt_priorities = 0; $cpt_priorities <= $diff_priorities; $cpt_priorities++){
					if(isset($not_priorities[$cpt_priorities])){
						$selected_times[] = $not_priorities[$cpt_priorities];
					}
				}
			}
			else{
				// Select only priorities, do nothing more
			}
		}

		return $selected_times;
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
		$has_voucher = sanitize_text_field(filter_input( INPUT_POST, 'has_voucher' ));
		$appointment = sanitize_text_field(filter_input( INPUT_POST, 'appointment' ));
		$appointment_date = sanitize_text_field(filter_input( INPUT_POST, 'appointment_date' ));
		$appointment_time = sanitize_text_field(filter_input( INPUT_POST, 'appointment_time' ));
		$aquos_id = sanitize_text_field(filter_input( INPUT_POST, 'aquos_id' ));

		if ( !empty( $has_voucher ) ) {
			$cart_item_data['has_voucher'] = $has_voucher;
		}
		if ( !empty( $appointment ) ) {
			$cart_item_data['appointment'] = $appointment;
		}
		if ( !empty( $appointment_date ) ) {
			$cart_item_data['appointment_date'] = $appointment_date;
		}
		if ( !empty( $appointment_time ) ) {
			$cart_item_data['appointment_time'] = $appointment_time;
		}
		if ( !empty( $aquos_id ) ) {
			$cart_item_data['aquos_id'] = $aquos_id;
		}

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

		//print_r($cart_item);
		if ( !empty( $cart_item['appointment'] ) ) {
			$item_data[] = array(
				'key'     => __( 'Appointment', 'tmsm-aquos-spa-booking' ),
				'value'   => wc_clean( $cart_item['appointment'] ),
				'display' => '',
			);
		}

		if ( isset( $cart_item['has_voucher'] ) ) {
			$item_data[] = array(
				'key'     => __( 'Has Voucher', 'tmsm-aquos-spa-booking' ),
				'value'   => ($cart_item['has_voucher'] == '1'? __( 'Yes', 'tmsm-aquos-spa-booking' ): __( 'No', 'tmsm-aquos-spa-booking' )),
				'display' => '',
			);
		}

		if ( !empty( $cart_item['aquos_id'] ) ) {
			$item_data[] = array(
				'key'     => __( 'Aquos ID', 'tmsm-aquos-spa-booking' ),
				'value'   => wc_clean( $cart_item['aquos_id'] ),
				'display' => '',
			);
		}

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
	 *
	 * @throws WC_Data_Exception Throws exception when invalid data is found.
	 */
	public function woocommerce_checkout_create_order_line_item_appointment( $item, $cart_item_key, $values, $order ) {

		$variation_id = isset( $values['variation_id'] ) && ! empty( $values['variation_id'] ) ? $values['variation_id'] : $values['product_id'];

		if ( ! empty( $values['appointment'] ) ) {
			$item->add_meta_data( '_appointment_date', $values['appointment_date'], true );
			$item->add_meta_data( '_appointment_time', $values['appointment_time'], true );
			$item->add_meta_data( '_appointment', $values['appointment'], true );
			$item->add_meta_data( '_has_voucher', $values['has_voucher'], true );
			$item->add_meta_data( '_aquos_id', $values['aquos_id'], true );

			$order->set_shipping_first_name('');
			$order->set_shipping_last_name('');
			$order->set_shipping_address_1('');
			$order->set_shipping_address_2('');
			$order->set_shipping_city('');
			$order->set_shipping_country('');
			$order->add_meta_data('_appointment', 'yes', true);
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
			$strings[]           = '<strong class="wc-item-meta-label">' . __( 'Appointment:', 'tmsm-aquos-spa-booking' ) . '</strong> '.  esc_html($item['_appointment']);
		}

		if ( !empty($item['_has_voucher'])) {
			$strings[]           = '<strong class="wc-item-meta-label">' . __( 'Has Voucher:', 'tmsm-aquos-spa-booking' ) . '</strong> '. ($item['_has_voucher'] == 1 ? __( 'Yes', 'tmsm-aquos-spa-booking' ) : __( 'No', 'tmsm-aquos-spa-booking' ) );
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
			if(!empty($value['appointment'])){
				$value['data']->set_virtual(true);
				if(!empty($value['appointment']) && $value['has_voucher'] == 1){
					$value['data']->set_price(0);
				}
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
	 * @param WC_Order|int $order
	 *
	 * @return bool
	 */
	private function order_has_appointment($order){

		$order_id = WC_Order_Factory::get_order_id( $order );

		$order = wc_get_order($order_id);

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
	 * If Cart has at least one appointment
	 *
	 * @return bool
	 */
	private function cart_has_appointment(){

		$cart_items = WC()->cart->get_cart_contents();
		$cart_has_appointment = array_map( function( $cart_item ) { return $cart_item['appointment']; }, $cart_items );

		return (count($cart_has_appointment) > 0);
	}


	/**
	 * If Cart has at least one appointment
	 *
	 * @return bool
	 */
	private function cart_has_atleastonevoucher(){

		$cart_items = WC()->cart->get_cart_contents();
		$cart_has_voucher = array_map( function( $cart_item ) { return $cart_item['has_voucher']; }, $cart_items );

		return (array_sum($cart_has_voucher) > 0);
	}

	/**
	 * If Cart has at least one appointment
	 *
	 * @return bool
	 */
	private function cart_has_voucheronly(){

		$cart_items = WC()->cart->get_cart_contents();
		$cart_has_voucher = array_map( function( $cart_item ) { return $cart_item['has_voucher']; }, $cart_items );

		return array_sum( $cart_has_voucher ) == count( $cart_items );
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
	 * Diplay error in submission failed
	 *
	 * @param int $order_id
	 *
	 */
	function woocommerce_thankyou_order_error( int $order_id ) {

		$order = wc_get_order($order_id);

		if ( self::order_has_appointment( $order ) === true ) {

			if($order->get_meta('_appointment_error', true) == 'yes'){
				echo '<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">'.__( 'Submission failed, we will contact you shortly.', 'tmsm-aquos-spa-booking' ).'</p>';
			}

		}

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
	 * Remove Appointments that are expired, too old = 2 hours
	 */
	public function woocommerce_check_cart_items_expire(){
		if( is_cart() || is_checkout() ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				if(!empty($cart_item['appointment']) && !empty($cart_item['timestamp_added'])){
					$_product = $cart_item['data'];
					if( time() > ( $cart_item['timestamp_added'] + 3600 * get_option( 'tmsm_aquos_spa_booking_cartexpirehours', 2 ))){
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

	/**
	 * Filters the post title.
	 *
	 * @param string $title The post title.
	 * @param int    $id    The post ID.
	 *
	 * @return string
	 */
	public function the_title( $title, $id ) {

		if ( $id === intval( get_option( 'woocommerce_checkout_page_id' ) ) ) {
			$title = __( 'Appointment', 'tmsm-aquos-spa-booking' );
		}

		return $title;
	}

	/**
	 * Filters the checkout button text
	 *
	 * @param $text
	 *
	 * @return string
	 */
	public function woocommerce_order_button_text($text){
		global $post;

		if(self::cart_has_appointment()){
			$text = __( 'Book this appointment', 'tmsm-aquos-spa-booking' );
		}

		return $text;
	}

	/**
	 * Filters the list of CSS body class names
	 *
	 * @since 2.8.0
	 *
	 * @param string[] $classes An array of body class names.
	 * @param string[] $class   An array of additional class names added to the body.
	 *
	 * @return string[]
	 */
	public function body_class($classes, $class){
		global $post;

		$pages = [intval(get_option( 'woocommerce_checkout_page_id' )), intval(get_option( 'woocommerce_cart_page_id' ))];

		if(in_array($post->ID, $pages) && self::cart_has_appointment()){
			$classes[] = 'tmsm-aquos-spa-booking-checkout-has-appointments';
		}
		if(in_array($post->ID, $pages) && self::cart_has_atleastonevoucher()){
			$classes[] = 'tmsm-aquos-spa-booking-checkout-has-atleastonevoucher';
		}
		if(in_array($post->ID, $pages) && self::cart_has_voucheronly()){
			$classes[] = 'tmsm-aquos-spa-booking-checkout-has-voucheronly';
		}

		return $classes;
	}

	/**
	 * Make orders not needing processing if it has appointments
	 *
	 * @param bool $item_needs_processing
	 * @param WC_Product $product
	 * @param int $order_id
	 *
	 * @return bool
	 */
	public function woocommerce_order_item_needs_processing($item_needs_processing, $product, $order_id){

		if($this->order_has_appointment($order_id)){
			$item_needs_processing = false;
		}

		return $item_needs_processing;
	}

	/**
	 * "Virtual only" column content
	 *
	 * @param $column
	 * @param $post_id
	 */
	function shop_order_posts_custom_column_appointment( $column, $post_id )
	{
		global $post, $the_order;

		if ( empty( $the_order ) || $the_order->get_id() !== $post->ID ) {
			$the_order = wc_get_order( $post->ID );
		}

		switch ( $column ) {
			case 'shipping_address':
				if ( $the_order->get_meta('_appointment', true) === 'yes') {
					echo '<span class="description" style="display: inline-block; margin-top: -10px; margin-left: -10px;"><span class="dashicons dashicons-calendar" style=""></span> '.__( 'Appointment', 'tmsm-aquos-spa-booking' ).'</span>';
				}
				break;
		}
	}


	/**
	 * Order Status Appointment for COD payments (not free)
	 *
	 * @param string $status
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	function order_status_appointment_cod( $status, $order ) {

		if ( self::order_has_appointment( $order ) ) {
			$status = 'wc-appointment';
		}

		return $status;
	}

	/**
	 * Order Status Appointment for Voucher payments (free)
	 *
	 * @param string $status
	 * @param int $order_id
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	function order_status_appointment_voucher( $status, $order_id, $order ) {

		if ( self::order_has_appointment( $order ) ) {
			$status = 'wc-appointment';
		}

		return $status;
	}


	/**
	 * Send a response to ajax request, as JSON.
	 *
	 * @param mixed $response
	 */
	private function _ajax_return( $response = true ) {
		echo json_encode( $response );
		exit;
	}

	/**
	 * Returns list of cron schedules.
	 *
	 * @return array
	 */
	private function _get_schedules() {

		// Get list of schedules.
		$schedules = wp_get_schedules();

		// Append a "Once Only" schedule.
		$schedules['once'] = array(
			'display' => __( 'Once Only', 'tmsm-aquos-spa-booking' ),
		);

		// Get list of jobs assigned to schedules.
		// Using "private" function is really naughty, but it's the best option compared to querying db/options.
		$cron_array = _get_cron_array();

		// Consistent timestamp for seconds until due.
		$now = time();

		// Add child cron events to schedules.
		foreach ( $cron_array as $timestamp => $jobs ) {
			foreach ( $jobs as $hook => $events ) {
				foreach ( $events as $key => $event ) {
					$event['hook']        = $hook;
					$event['timestamp']   = $timestamp;
					$event['seconds_due'] = $timestamp - $now;

					// The cron array also includes events without a recurring schedule.
					$scheduled = empty( $event['schedule'] ) ? 'once' : $event['schedule'];

					$schedules[ $scheduled ]['events'][] = $event;
				}
			}
		}

		// We need to change the associative array (map) into an indexed one (set) for easier use in collection.
		$set = array();
		foreach ( $schedules as $name => $schedule ) {
			$schedule['name'] = $name;
			$set[]            = $schedule;
		}

		return $set;
	}

	/**
	 * Returns list of product categories.
	 *
	 * @return array
	 */
	private function _get_product_categories() {

		error_log('_get_product_categories');
		// Get "product_cat" Terms With Parent as an Option
		$settings_maincategory  = get_option( 'tmsm_aquos_spa_booking_productcat', 0 );
		$product_categories = get_terms( 'product_cat', [
			'hide_empty' => true,
			'child_of' => !empty($settings_maincategory) ? $settings_maincategory: 0,
			'orderby'    => 'parent',
		]);

		return $product_categories;
	}

	/**
	 * Returns list of product attributes.
	 *
	 * @return array
	 */
	private function _get_product_attributes() {

		error_log('_get_product_attributes');
		$product_id = sanitize_text_field( $_REQUEST['product'] );
		$product = wc_get_product($product_id);

		if ( ! empty( $product ) && $product instanceof WC_Product ) {

			if($product->is_type( 'variable' ) && $product instanceof WC_Product_Variable) {
				$variations = [];
				$attributes = [];
				foreach ( $product->get_available_variations() as $variation_data ) {

					$variation = wc_get_product( $variation_data['variation_id'] );

					if(empty($variation)){
						continue;
					}
					if ( ! ( $variation instanceof WC_Product_Variation ) ) {
						continue;
					}

					$variation_name = esc_js($variation->get_name(). (wc_get_formatted_variation($variation, true, false, true ) ? ' ㅡ '.wc_get_formatted_variation($variation, true, false, true ): '') );

					if($variation->get_attribute('format-bon-cadeau')){
						error_log($variation->get_attribute('format-bon-cadeau'));
						$variation_name = str_replace(', '.$variation->get_attribute('format-bon-cadeau'), '', $variation_name);
					}

					$variation_name = str_replace($product->get_name(). ' - ', '', $variation_name);
					$variation_name = str_replace($product->get_name(). ' ㅡ ', '', $variation_name);
					$variation_name = str_replace($product->get_name(). ' — ', '', $variation_name);

					$aquos_id = get_post_meta( $variation->get_id(), '_aquos_id', true);
					if(empty($aquos_id)){
						continue;
					}
					$sku = substr( $variation->get_sku(), 0, 2 ) === 'E-' ? substr( $variation->get_sku(), 2 ) : $variation->get_sku();

					$variations[$sku] = [
						'id' => esc_js($variation->get_id()),
						'permalink' => esc_js($variation->get_permalink()),
						'thumbnail' => get_the_post_thumbnail_url($product_id) ? get_the_post_thumbnail_url($product_id) : '',
						'price' => html_entity_decode(wp_strip_all_tags($variation->get_price_html())),
						'sku' => esc_js($variation->get_sku()),
						'name' => $variation_name,
						'attributes' => ( wp_json_encode( $variation->get_variation_attributes() ) ),
					];

				}
				$jsondata['variations'] = $variations;
				$jsondata['attributes'] = [];
				foreach($product->get_variation_attributes() as $name => $options ){

					$tax    = get_taxonomy( $name );
					$labels = get_taxonomy_labels( $tax );
					//error_log(print_r($tax, true));
					//error_log(print_r($labels, true));
					$options = [];
					$options['label'] = $labels->singular_name;
					$options['slug'] = $name;
					$options['productid'] = $product->get_id();
					$options['terms'] = wc_get_product_terms( $product->get_id(), $name, array( 'fields' => 'all' ));
					$jsondata['attributes'][$name] = $options;
					$attributes[] = $options;
				}

				//error_log(print_r($jsondata, true));
			}
			else{
				$errors[] = __('Product is not variable', 'tmsm-aquos-spa-booking');
			}

		}
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('$attributes:');
			error_log(print_r($attributes, true));
		}
		return $attributes;
	}

	/**
	 * Returns list of product variations
	 *
	 * @return array
	 */
	private function _get_product_variations() {

		error_log('_get_product_variations');
		$product_id = sanitize_text_field( $_REQUEST['product'] );
		$product = wc_get_product($product_id);

		$variations = [];

		if ( ! empty( $product ) && $product instanceof WC_Product ) {

			if($product->is_type( 'variable' ) && $product instanceof WC_Product_Variable) {

				foreach ( $product->get_available_variations() as $variation_data ) {

					$variation = wc_get_product( $variation_data['variation_id'] );

					if(empty($variation)){
						continue;
					}
					if ( ! ( $variation instanceof WC_Product_Variation ) ) {
						continue;
					}

					error_log('variation '.$variation->get_name());

					$variation_name = esc_js($variation->get_name(). (wc_get_formatted_variation($variation, true, false, true ) ? ' ㅡ '.wc_get_formatted_variation($variation, true, false, true ): '') );

					if($variation->get_attribute('format-bon-cadeau')){
						error_log($variation->get_attribute('format-bon-cadeau'));
						$variation_name = str_replace(', '.$variation->get_attribute('format-bon-cadeau'), '', $variation_name);
					}

					$variation_name = str_replace($product->get_name(). ' - ', '', $variation_name);
					$variation_name = str_replace($product->get_name(). ' ㅡ ', '', $variation_name);
					$variation_name = str_replace($product->get_name(). ' — ', '', $variation_name);

					$aquos_id = get_post_meta( $variation->get_id(), '_aquos_id', true);
					if(empty($aquos_id)){
						continue;
					}
					$sku = substr( $variation->get_sku(), 0, 2 ) === 'E-' ? substr( $variation->get_sku(), 2 ) : $variation->get_sku();

					$variations[] = [
						'id' => esc_js($variation->get_id()),
						'permalink' => esc_js($variation->get_permalink()),
						'thumbnail' => get_the_post_thumbnail_url($product_id) ? get_the_post_thumbnail_url($product_id) : '',
						'price' => html_entity_decode(wp_strip_all_tags($variation->get_price_html())),
						'sku' => esc_js($variation->get_sku()),
						'name' => $variation_name,
						'attributes' => ( wp_json_encode( $variation->get_variation_attributes() ) ),
					];

				}
			}
			else{
				$errors[] = __('Product is not variable', 'tmsm-aquos-spa-booking');
			}

		}
		else{
			$errors[] = __('Product doesnt not exist', 'tmsm-aquos-spa-booking');
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			//error_log('$variations:');
			//error_log(print_r($variations, true));
		}
		return $variations;
	}

	/**
	 * Returns list of product categories.
	 *
	 * @return array
	 */
	private function _get_products() {

		error_log('_get_products');

		$product_category_id = null;

		if(isset($_REQUEST['productcategory'])){
			$product_category_id = sanitize_text_field( $_REQUEST['productcategory'] );
		}

		$args = array(
			'return'  => 'ids',
			'limit' => -1,
			'orderby' => 'name',
		);
		if(!empty($product_category_id)){
			$product_category = get_term( $product_category_id, 'product_cat');
			$args['category'] = $product_category->slug;
		}

		$products_ids = wc_get_products( $args );
		$products = [];
		if(!empty($products_ids)){
			foreach($products_ids as $key => $product_id){
				$product = wc_get_product($product_id);

				//$products[$product->get_id()] = [
				$products[] = [
					'id' => esc_js($product->get_id()),
					'permalink' => esc_js($product->get_permalink()),
					'thumbnail' => get_the_post_thumbnail_url($product_id) ? get_the_post_thumbnail_url($product_id) : '',
					'price' => html_entity_decode(wp_strip_all_tags($product->get_price_html())),
					'sku' => esc_js($product->get_sku()),
					'name' => esc_js($product->get_name()),
					'variable' => esc_js($product->is_type( 'variable' )),
				];


				/*if(!$product->is_type( 'variable' )){

					$aquos_id = get_post_meta( $product_id, '_aquos_id', true);
					if(empty($aquos_id)){
						continue;
					}

					$sku = substr( $product->get_sku(), 0, 2 ) === 'E-' ? substr( $product->get_sku(), 2 ) : $product->get_sku();
					error_log($product->get_sku());
					error_log($sku);

					$product_and_variations[$sku] = [
						'id' => esc_js($product->get_id()),
						'permalink' => esc_js($product->get_permalink()),
						'thumbnail' => get_the_post_thumbnail_url($product_id) ? get_the_post_thumbnail_url($product_id) : '',
						'price' => html_entity_decode(wp_strip_all_tags($product->get_price_html())),
						'sku' => esc_js($product->get_sku()),
						'name' => esc_js($product->get_name()),
					];
				}
				else{
					if($product->is_type( 'variable' ) ){
						foreach ( $product->get_available_variations() as $variation_data ) {

							$variation = wc_get_product( $variation_data['variation_id'] );

							if(empty($variation)){
								continue;
							}

							$variation_name = esc_js($variation->get_name(). (wc_get_formatted_variation($variation, true, false, true ) ? ' ㅡ '.wc_get_formatted_variation($variation, true, false, true ): '') );

							if($variation->get_attribute('format-bon-cadeau')){
								error_log($variation->get_attribute('format-bon-cadeau'));
								$variation_name = str_replace(', '.$variation->get_attribute('format-bon-cadeau'), '', $variation_name);
							}

							$aquos_id = get_post_meta( $variation->get_id(), '_aquos_id', true);
							if(empty($aquos_id)){
								continue;
							}
							$sku = substr( $variation->get_sku(), 0, 2 ) === 'E-' ? substr( $variation->get_sku(), 2 ) : $variation->get_sku();
							error_log($variation->get_sku());
							error_log($sku);

							$product_and_variations[$sku] = [
								'id' => esc_js($variation->get_id()),
								'permalink' => esc_js($variation->get_permalink()),
								'thumbnail' => get_the_post_thumbnail_url($product_id) ? get_the_post_thumbnail_url($product_id) : '',
								'price' => html_entity_decode(wp_strip_all_tags($variation->get_price_html())),
								'sku' => esc_js($variation->get_sku()),
								'name' => $variation_name,
							];

						}
					}
				}*/


			}
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			//error_log('$products:');
			//error_log(print_r($products, true));
		}
		return $products;
	}

	/**
	 * Get Times from Web Service
	 *
	 * @since    1.0.0
	 *
	 * @return array
	 */
	private function _get_times() {

		$product_category_id = sanitize_text_field( $_REQUEST['productcategory'] );
		$product_id          = sanitize_text_field( $_REQUEST['product'] );
		$date                = sanitize_text_field( $_REQUEST['date'] );
		$times = [];

		$product = wc_get_product( $product_id );

		$aquos_id = get_post_meta( $product_id, '_aquos_id', true );

		$errors   = array(); // Array to hold validation errors
		$jsondata = array(); // Array to pass back data

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'ajax_times' );
		}

		if(empty( $aquos_id )){
			$errors[] = __( 'This product is not bookable', 'tmsm-aquos-spa-booking' );
		}
		if(empty( $product_id )){
			$errors[] = __( 'Product information is missing', 'tmsm-aquos-spa-booking' );
		}
		if(empty( $date )){
			$errors[] = __( 'Date is missing', 'tmsm-aquos-spa-booking' );
		}
		if(empty( $product_category_id )){
			$errors[] = __( 'Product category is missing', 'tmsm-aquos-spa-booking' );
		}

		if ( !empty( $errors ) ) {
			$jsondata['success'] = false;
			$jsondata['errors']  = $errors;
		}

		// Call web service
		$settings_webserviceurl = get_option( 'tmsm_aquos_spa_booking_webserviceurltimes' );
		if ( ! empty( $settings_webserviceurl ) ) {

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'url before:' . $settings_webserviceurl );
			}

			$patterns     = [
				'/{date}/',
				'/{product_id}/',
				'/{site_id}/',
				'/{site_name}/'
			];
			$replacements = [
				esc_html( str_replace( '-', '', $date ) ),
				esc_html( $aquos_id ),
				( is_multisite() ? get_current_blog_id() : 0 ),
				esc_html( get_bloginfo( 'name' ) ),
			];

			// Replace keywords in url
			$settings_webserviceurl = preg_replace( $patterns, $replacements, $settings_webserviceurl );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'url after:' . $settings_webserviceurl );
			}

			// Connect with cURL
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_URL, $settings_webserviceurl );
			$result = curl_exec( $ch );
			curl_close( $ch );

			if(empty($result)){
				$errors[] = __( 'Web service is not available', 'tmsm-aquos-spa-booking' );
			}
			else{
				$result_array = json_decode( $result, true );

				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( var_export( $result_array, true ) );
				}

				if(!empty($result_array['Status']) && $result_array['Status'] == 'true'){

					foreach($result_array['Schedules'] as $schedule){
						$schedule_hourminutes = explode(':', $schedule['Hour']);
						$times[] = [
						'hour' => $schedule_hourminutes[0],
						'minutes' => $schedule_hourminutes[1],
						'hourminutes' => $schedule['Hour'],
						'priority' => $schedule['Priority'],
						];
					}
				}
				else{
					if(!empty($result_array['ErrorCode']) && !empty($result_array['ErrorMessage'])){
						$errors[] = sprintf(__( 'Error code %s: %s', 'tmsm-aquos-spa-booking' ), $result_array['ErrorCode'], $result_array['ErrorMessage']);
					}
				}
			}
		}

		//$times = self::sort_times_by_priority( $times );
		$times = self::select_times( $times );
		//$times = self::sort_times_by_hourminutes( $times );

		if ( count( $times ) == 0 ) {
			$errors[] = __( 'No time slot available for this day and this product', 'tmsm-aquos-spa-booking' );
		}

		//$jsondata['times'] = $times;

		//$jsondata['success'] = true;
		//$jsondata['errors'] = $errors;

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('$errors:');
			error_log(print_r($errors, true));
			//error_log('$times:');
			//error_log(print_r($times, true));
		}

		return $times;
	}

	/**
	 * Do actions when order status changed to appointment
	 *
	 * @param int      $order_id
	 * @param WC_Order $order
	 *                       
	 */
	public function change_order_status_appointment (int $order_id, WC_Order $order){
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('change_order_status_appointment for order '.$order_id);
		}

		//$background_process = new Tmsm_Aquos_Spa_Booking_Background_Process();
		$background_process = $GLOBALS['tmsm_asb_bp'];

		$item = ['order_id' => $order_id];
		$background_process->push_to_queue( $item );

		$background_process->save()->dispatch();
	}
}
