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

		if(!is_admin()){
			// Disable MailChimp for WooCommerce Javascript when ordering appointments
			if(self::cart_has_appointment()){
				wp_dequeue_script('mailchimp-woocommerce');
			}

			/*wp_deregister_script('bootstrap-datepicker');
			wp_enqueue_script( 'bootstrap-datepicker', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js', array( 'jquery', 'bootstrap' ), null, true );

			if ( $this->get_locale() !== 'en' ) {
				wp_deregister_script( 'bootstrap-datepicker-' . $this->get_locale() );
				wp_enqueue_script( 'bootstrap-datepicker-' . $this->get_locale(), 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.' . $this->get_locale() . '.min.js',
					array( 'jquery', 'bootstrap', 'bootstrap-datepicker' ), null, true );
			}*/



			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquos-spa-booking-public'.( !(in_array('administrator',  wp_get_current_user()->roles) || (defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG)) ?'.min' : '' ).'.js', array( 'jquery', 'moment', 'wp-util', 'bootstrap-datepicker', 'wp-api' ), $this->version, true );


			$datebeforeforbidden = DateTime::createFromFormat('Y-m-d', get_option( 'tmsm_aquos_spa_booking_datebeforeforbidden', '' ));

			$today = new \DateTime();

			$startdate = new \DateTime();
			$startdate->modify('+'.get_option( 'tmsm_aquos_spa_booking_daysrangefrom', 1 ). ' days');

			if($datebeforeforbidden > $startdate){
				$startdate = $datebeforeforbidden;
			}

			$interval = $today->diff($startdate);
			$daysrangefrom = $interval->format('%a');


			$enddate = new \DateTime();
			$enddate->modify('+'.get_option( 'tmsm_aquos_spa_booking_daysrangeto', 60 ). ' days');

			// Add initial data to CronPixie JS object so it can be rendered without fetch.
			// Also add translatable strings for JS as well as reference settings.
			$data = array(
				'strings' => array(
					'loading'               => __( 'Loading', 'tmsm-aquos-spa-booking' ),
					'notimeslot'            => __( 'No timeslot', 'tmsm-aquos-spa-booking' ),
					'no_selection'          => __( 'No selection', 'tmsm-aquos-spa-booking' ),
					'no_events'             => _x( '(none)', 'no event to show', 'tmsm-aquos-spa-booking' ),
					'due'                   => _x( 'due', 'label for when cron event date', 'tmsm-aquos-spa-booking' ),
					'now'                   => _x( 'now', 'cron event is due now', 'tmsm-aquos-spa-booking' ),
					'passed'                => _x( 'passed', 'cron event is over due', 'tmsm-aquos-spa-booking' ),
					'weeks_abrv'            => _x( 'w', 'displayed in interval', 'tmsm-aquos-spa-booking' ),
					'days_abrv'             => _x( 'd', 'displayed in interval', 'tmsm-aquos-spa-booking' ),
					'hours_abrv'            => _x( 'h', 'displayed in interval', 'tmsm-aquos-spa-booking' ),
					'minutes_abrv'          => _x( 'm', 'displayed in interval', 'tmsm-aquos-spa-booking' ),
					'seconds_abrv'          => _x( 's', 'displayed in interval', 'tmsm-aquos-spa-booking' ),
					'run_now'               => _x( 'Run event now.', 'Title for run now icon', 'tmsm-aquos-spa-booking' ),
					'livesearchplaceholder' => _x( 'Search', 'Bootstrap Select live search placeholder', 'tmsm-aquos-spa-booking' ),
				),
				'calendar' => [
					'dateselection' => esc_js( get_option( 'tmsm_aquos_spa_booking_dateselection', 'calendar' ) ),
					'daysrangefrom' => esc_js( $daysrangefrom ),
					'daysrangeto'   => esc_js( get_option( 'tmsm_aquos_spa_booking_daysrangeto', 60 ) ),
					'enddate'       => $enddate->format( 'Y-m-d' ),
					'startdate'     => $startdate->format( 'Y-m-d' ),
				],
				'role'         => current_user_can( 'edit_posts' ),
				'locale'       => $this->get_locale(),
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'courseplugin' => ( class_exists( 'Tmsm_Aquatonic_Course_Booking' )
				                    && get_option( 'tmsm_aquos_spa_booking_enablecourseintegration', 'no' ) === 'yes' ),
				'nonce'        => wp_create_nonce( 'tmsm-aquos-spa-booking-nonce-action' ),
				'data'         => array(
					'havevoucher'       => [
						'yes' => [
							'name'  => __( 'I have a voucher', 'tmsm-aquos-spa-booking' ),
							'slug'  => 'yes',
							'value' => 1,
						],
						'no'  => [
							'name'  => __( 'I don\'t have any voucher', 'tmsm-aquos-spa-booking' ),
							'slug'  => 'no',
							'value' => 0,
						],
					],
					//'schedules' => $this->_get_schedules(),
					'productcategories' => $this->_get_product_categories(),
					'products'          => $this->_get_products(),
					'productattributes' => array(),
					'productvariations' => array(),
					'choices'           => array(),
					'times'             => array(),
					'weekdays'          => array(),
				),
			);
			wp_localize_script( $this->plugin_name, 'TmsmAquosSpaBookingApp', $data );
		}

	}


	/**
	 * Register the shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'tmsm-aquos-spa-booking', array( $this, 'shortcode') );
	}


	/**
	 * Modify WP_Query with _bookable meta field
	 *
	 * @param $wp_query_args
	 * @param $query_vars
	 * @param $data_store_cpt
	 *
	 * @return mixed
	 */
	public function woocommerce_product_data_store_cpt_get_products_query_bookable($wp_query_args, $query_vars, $data_store_cpt){

		$meta_key = '_bookable'; // The custom meta_key

		if ( ! empty( $query_vars[$meta_key] ) ) {
			$wp_query_args['meta_query'][] = array(
				'key'     => $meta_key,
				'value'   => esc_attr( $query_vars[$meta_key] ),
				'compare' => '=', // <=== Here you can set other comparison arguments
			);
		}

		return $wp_query_args;
	}

	/**
	 * Button Class No Sate
	 *
	 * @return string
	 */
	private static function button_class_nostate(){
		$theme = wp_get_theme();
		$buttonclass = '';
		if ( 'StormBringer' == $theme->get( 'Name' ) || 'stormbringer' == $theme->get( 'Template' ) ) {
			$buttonclass = 'btn';
		}
		if ( 'OceanWP' == $theme->get( 'Name' ) || 'oceanwp' == $theme->get( 'Template' ) ) {
			$buttonclass = 'button';
		}
		return $buttonclass;
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
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public function shortcode($atts) {
		$atts = shortcode_atts( array(
			'option' => '',
		), $atts, 'tmsm-aquos-spa-booking' );


		if(is_admin()){
			return;
		}

		if(empty(WC()->cart)){
			return;
		}
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

		// Check cart items if any appointment has expired
		do_action( 'woocommerce_check_cart_items' );

		$output = '';
		if($cart_has_products){
			$output = '<p id="tmsm-aquos-spa-booking-emptycartfirst" class="'.self::alert_class_error().'" >'.sprintf(__( 'Your cart contains products other than appointments, please <a href="%s">empty your cart or complete the order</a> before booking an appointment.', 'tmsm-aquos-spa-booking' ), wc_get_cart_url()).'</p>';
		}
		else {

			$output = '
			<form id="tmsm-aquos-spa-booking-form"  class="tmsm-aquos-spa-booking-form-'.get_option( 'tmsm_aquos_spa_booking_dateselection', 'calendar' ).'">
			
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
			<select id="tmsm-aquos-spa-booking-categories-select" data-live-search="true"  title="' . esc_attr__( 'No selection', 'tmsm-aquos-spa-booking' ) . '">
			</select>
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-products-container" >
			<div id="tmsm-aquos-spa-booking-products-inner">
			<h3>' . __( 'Pick your treatment:', 'tmsm-aquos-spa-booking' ) . '</h3>
			<p id="tmsm-aquos-spa-booking-products-loading">' . __( 'Loading', 'tmsm-aquos-spa-booking' ) . '</p>
			<select id="tmsm-aquos-spa-booking-products-select" data-live-search="true" data-size="16" title="' . esc_attr__( 'No selection', 'tmsm-aquos-spa-booking' ) . '"></select>
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-attributes-container" >
			<div id="tmsm-aquos-spa-booking-attributes-inner">
			<h3>' . __( 'Pick your variation:', 'tmsm-aquos-spa-booking' ) . '</h3>
			
			<p id="tmsm-aquos-spa-booking-attributes-loading">' . __( 'Loading', 'tmsm-aquos-spa-booking' ) . '</p>
			<p id="tmsm-aquos-spa-booking-attributes-empty">' . __( 'No options to pick', 'tmsm-aquos-spa-booking' ) . '</p>
			<ul id="tmsm-aquos-spa-booking-attributes-list" class="list-unstyled"></ul>
			<p class="tmsm-aquos-spa-booking-attributes-reset-confirm">
			<a href="#" id="tmsm-aquos-spa-booking-attributes-cancel" class="'.self::button_class_default().'">' . __( 'Reset your options', 'tmsm-aquos-spa-booking' ) . '</a>
			<a href="#" id="tmsm-aquos-spa-booking-attributes-confirm" class="'.self::button_class_primary().'">' . __( 'Confirm your options', 'tmsm-aquos-spa-booking' ) . '</a>
			</p>
			<div style="display:none"> 
			<select id="tmsm-aquos-spa-booking-variations-select" title="' . esc_attr__( 'No selection', 'tmsm-aquos-spa-booking' ) . '"></select>
			</div>
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-choices-container" >
			<div id="tmsm-aquos-spa-booking-choices-inner">
			<h3>' . __( 'Pick your choice:', 'tmsm-aquos-spa-booking' ) . '</h3>
			<p id="tmsm-aquos-spa-booking-choices-loading">' . __( 'Loading', 'tmsm-aquos-spa-booking' ) . '</p>
			<select id="tmsm-aquos-spa-booking-choices-select"  title="' . esc_attr__( 'No selection', 'tmsm-aquos-spa-booking' ) . '"></select>
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-date-container" class="'.(get_option( 'tmsm_aquos_spa_booking_dateselection', 'calendar' )=== 'weekdays'?'tmsm-aquos-spa-booking-date-container-weekdays':'').'">
			<div id="tmsm-aquos-spa-booking-date-inner">
			'.(get_option( 'tmsm_aquos_spa_booking_dateselection', 'calendar' )!== 'calendar' ? '<h3>' . __( 'Pick your treatments timeslot:', 'tmsm-aquos-spa-booking' ) . '</h3>' : '<h3>' . __( 'Pick your treatments date:', 'tmsm-aquos-spa-booking' ) . '</h3>' ).'
			<button class="' . self::button_class_nostate() . ' '.(get_option( 'tmsm_aquos_spa_booking_dateselection', 'calendar' )=== 'calendar'?'hide':'').'" id="tmsm-aquos-spa-booking-weekdays-previous" >' . __( 'Previous Dates', 'tmsm-aquos-spa-booking' ) .                 '</button>
			<button class="' . self::button_class_nostate() . ' '.(get_option( 'tmsm_aquos_spa_booking_dateselection', 'calendar' )=== 'calendar'?'hide':'').'" id="tmsm-aquos-spa-booking-weekdays-next" >' . __( 'Next Dates', 'tmsm-aquos-spa-booking' ) . '</button>	
			<div id="tmsm-aquos-spa-booking-datepicker" class="panel panel-default" style="'.(get_option( 'tmsm_aquos_spa_booking_dateselection', 'calendar' )!== 'calendar'?'display:none;':'').'">
			</div>
			<ul id="tmsm-aquos-spa-booking-weekdays-list" class="nav nav-tabs nav-justified" style="'.(get_option( 'tmsm_aquos_spa_booking_dateselection', 'calendar' )!== 'weekdays'?'display:none;':'').'">' . __( 'Loading', 'tmsm-aquos-spa-booking' ) . '
			</ul>


			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-times-container" style="'.(get_option( 'tmsm_aquos_spa_booking_dateselection', 'calendar' )!== 'calendar'?'display:none;':'').'">
			<div id="tmsm-aquos-spa-booking-times-inner">
			<h3>' . __( 'Pick your treatments time:', 'tmsm-aquos-spa-booking' ) . '</h3>
			<p id="tmsm-aquos-spa-booking-date-display"></p>
			<p id="tmsm-aquos-spa-booking-times-loading">' . __( 'Loading', 'tmsm-aquos-spa-booking' ) . '</p>
			<p id="tmsm-aquos-spa-booking-times-error" style="display: none">' . __( 'No time results for this date.', 'tmsm-aquos-spa-booking' ) . ( !empty(get_option( 'tmsm_aquos_spa_booking_contactpage', '' )) ? '<br>'.sprintf(__( '<a href="#" id="tmsm-aquos-spa-booking-times-anotherdate">Select another date</a> or <a href="%s">contact us</a> for more information.', 'tmsm-aquos-spa-booking' ), get_permalink(get_option( 'tmsm_aquos_spa_booking_contactpage', '' ))): ''). '</p>
			<ul id="tmsm-aquos-spa-booking-times-list" class="list-unstyled"></ul>
			</div>
			</div>
			
			<div id="tmsm-aquos-spa-booking-course-times-container" style="'.(!class_exists('Tmsm_Aquatonic_Course_Booking')?'display:none;':'').'">
				<div id="tmsm-aquos-spa-booking-course-times-inner">
					<h3>' . __( 'Pick your course time:', 'tmsm-aquos-spa-booking' ) . '</h3>
					<p id="tmsm-aquos-spa-booking-course-times-loading">' . __( 'Loading', 'tmsm-aquos-spa-booking' ) . '</p>
					<p id="tmsm-aquos-spa-booking-course-times-error" style="display: none">' . __( 'No time results for this date.', 'tmsm-aquos-spa-booking' ) . '</p>
					<ul id="tmsm-aquos-spa-booking-course-times-list" class="list-unstyled"></ul>
				</div>
			</div>
			
			
			<div id="tmsm-aquos-spa-booking-confirm-container">
			
			<p id="tmsm-aquos-spa-booking-confirm-error"></p>
			<button class="' . self::button_class_default() . '" id="tmsm-aquos-spa-booking-cancel">' . __( 'Cancel', 'tmsm-aquos-spa-booking' ) . '</button>
			
			<button class="' . self::button_class_primary() . ' btn-lg text-center" id="tmsm-aquos-spa-booking-confirm">'. __( 'Add this appointment', 'tmsm-aquos-spa-booking' ) . '</button>
			
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
	public function template_havevoucher(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-havevoucher">
			<label class="radio-inline">
				<input type="radio" name="tmsm-aquos-spa-booking-voucher" id="tmsm-aquos-spa-booking-voucher{{ data.slug}}" value="{{ data.value}}" autocomplete="off"> {{ data.name}}
			</label>
		</script>
		<?php
	}


	/**
	 * Weekday Template
	 */
	public function template_weekday(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-weekday">
			{{ data.date_label_firstline }} <span class="secondline">{{ data.date_label_secondline }}</span>
			<ul class="tmsm-aquos-spa-booking-weekday-times list-unstyled" data-date="{{ data.date_computed }}" >
			</ul>
			<span class="glyphicon glyphicon-refresh glyphicon-spin" title="{{ TmsmAquosSpaBookingApp.strings.loading }}"></span>
		</script>

		<?php
	}

	/**
	 * Time Template
	 */
	public function template_time(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-time">
			<# if ( data.hourminutes != null) { #>
			<a class="tmsm-aquos-spa-booking-time-button <?php echo self::button_class_default(); ?> tmsm-aquos-spa-booking-time" href="#" data-date="{{ data.date }}" data-hour="{{ data.hour }}" data-minutes="{{ data.minutes }}" data-hourminutes="{{ data.hourminutes }}" data-priority="{{ data.priority }}">{{ data.hourminutes }} <# if ( TmsmAquosSpaBookingApp.role == "1" && data.priority == 1) { #> <!--*--><# } #></a> <a href="#" class="tmsm-aquos-spa-booking-time-change-label"><?php echo __( 'Change time', 'tmsm-aquos-spa-booking' ); ?></a>
			<# } else { #>
				{{  TmsmAquosSpaBookingApp.strings.notimeslot }}
			<# } #>


		</script>
		<?php
	}

	/**
	 * Course Time Template
	 */
	public function template_course_time(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-course-time">
			<# if ( data.hourminutes != null) { #>
			<a class="tmsm-aquos-spa-booking-course-time-button <?php echo self::button_class_default(); ?> tmsm-aquos-spa-booking-course-time" href="#" data-date="{{ data.date }}" data-hour="{{ data.hour }}" data-minutes="{{ data.minutes }}" data-hourminutes="{{ data.hourminutes }}" data-priority="{{ data.priority }}">{{ data.hourminutes }} <# if ( TmsmAquosSpaBookingApp.role == "1" && data.priority == 1) { #> <!--*--><# } #></a> <a href="#" class="tmsm-aquos-spa-booking-time-change-label"><?php echo __( 'Change time', 'tmsm-aquos-spa-booking' ); ?></a>
			<# } else { #>
				{{  TmsmAquosSpaBookingApp.strings.notimeslot }}
			<# } #>


		</script>
		<?php
	}

	/**
	 * Product Category Template
	 */
	public function template_product_category(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-product-category">
			<# if (data.parent !== <?php echo esc_html(get_option( 'tmsm_aquos_spa_booking_productcat', 0 )) ?>) { #>
			-&nbsp;&nbsp;
			<# } #>
			{{ data.name }}
		</script>
		<?php
	}

	/**
	 * Product Template
	 */
	public function template_product(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-product">
				{{ data.name }} <# if ( data.is_voucher == '0') { #> — {{ data.price }} <# } #>

		</script>
		<?php
	}

	/**
	 * Product Variation Template
	 */
	public function template_product_variation(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-variation">
			{{ data.name }} <# if ( data.is_voucher == '0') { #> — {{ data.price }} <# } #>
		</script>
		<?php
	}

	/**
	 * Product Variation Template
	 */
	public function template_product_attribute(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-product-attribute">
			<span style="display: none;">{{ data.label }}</span>
			<# _.each( data.terms, function(term, index) { #>

			<label class="radio-inline radio-inline-{{ data.slug }}" <# if ( ( term.name.indexOf('Sans') >= 0 )) { #> style="display:none"<# } #>>
			<input class="tmsm-aquos-spa-booking-term <# if ( ( term.name.indexOf('Sans') >= 0 )) { #> checked-default<# } #>" type="radio" id="{{ data.slug }}_v_{{ term.slug }}{{ data.productid }}" name="attribute_{{ data.slug }}" value="{{ term.slug }}" <# if ( ( term.name.indexOf('Sans') >= 0 )) { #> checked="checked"<# } #>>{{term.name}} <# if ( data.is_voucher == '0' && term.description ) { #>({{term.description}})<# } #>
			</label>
			<# }) #>

		</script>
		<?php
	}


	/**
	 * Choice Template
	 */
	public function template_choice(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-choice">
			{{ data.name }}
		</script>
		<?php
	}



	


	/**
	 * Ajax For Product Categories
	 *
	 * @since    1.0.0
	 */
	public function ajax_product_categories() {

		$this->ajax_checksecurity();
		$this->ajax_return( $this->_get_product_categories() );

	}

	/**
	 * Ajax For Products Attributes
	 *
	 * @since    1.0.0
	 */
	public function ajax_product_attributes() {

		$this->ajax_checksecurity();
		$this->ajax_return( $this->_get_product_attributes() );

	}

	/**
	 * Ajax check nonce security
	 */
	private function ajax_checksecurity(){
		$security = sanitize_text_field( $_REQUEST['nonce'] );

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data
		
		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquos-spa-booking-nonce-action' ) ) {
			$errors[] = __('Token security is not valid', 'tmsm-aquos-spa-booking');
			if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
				error_log('Token security is not valid');
			}
		}
		else {
			if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
				error_log( 'Token security is valid' );
			}
		}
		if(check_ajax_referer( 'tmsm-aquos-spa-booking-nonce-action', 'nonce' ) === false){
			$errors[] = __('Ajax referer is not valid', 'tmsm-aquos-spa-booking');
			if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
				error_log('Ajax referer is not valid');
			}
		}
		else{
			if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
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

		$this->ajax_checksecurity();
		$this->ajax_return( $this->_get_products() );

	}

	/**
	 * Ajax For Products Variations
	 *
	 * @since    1.0.0
	 */
	public function ajax_product_variations() {

		$this->ajax_checksecurity();
		$this->ajax_return( $this->_get_product_variations() );

	}

	/**
	 * Ajax For Times
	 *
	 * @since    1.0.0
	 */
	public function ajax_times() {

		$this->ajax_checksecurity();
		$this->ajax_return( $this->_get_times() );

	}


	/**
	 * Ajax For Add To Cart
	 *
	 * @since    1.0.0
	 *
	 * @throws Exception
	 */
	public static function ajax_addtocart() {

		$selecteddata_array = isset( $_POST['selecteddata'] ) ? $_POST['selecteddata'] : array();

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		$nonce = sanitize_text_field( $_POST['nonce'] );
		$product_category_id = sanitize_text_field( $selecteddata_array['productcategory'] );
		$is_voucher = sanitize_text_field( $selecteddata_array['is_voucher'] );
		$product_id = sanitize_text_field( $selecteddata_array['product'] );
		$productvariation_id = sanitize_text_field( $selecteddata_array['productvariation'] );
		$choice_id = sanitize_text_field( $selecteddata_array['choice'] );
		$date = sanitize_text_field( $selecteddata_array['date'] );
		$hourminutes = sanitize_text_field( $selecteddata_array['hourminutes'] );

		$product = wc_get_product($product_id);
		if(!empty($productvariation_id)){
			$productvariation = wc_get_product($productvariation_id);
			$product = $productvariation;
			$product_id = $productvariation_id;
		}
		if(!empty($choice_id)){
			$aquos_id = $choice_id;
		}

		// Product existance
		if ( empty( $product ) ) {
			$errors[] = __('Product not found', 'tmsm-aquos-spa-booking');
		}
		else{
			if(empty($aquos_id)){
				$aquos_id = sanitize_text_field( $product->get_meta('_aquos_id', true ));
			}


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

			$variation_data = array();
			$variation = wc_get_product($variation_id);
			if( $variation instanceof WC_Product_Variation) {
				$variation_data = $variation->get_variation_attributes();

				// Add all attributes except (hardcoded) voucher attribute
				unset($variation_data['attribute_pa_format-bon-cadeau']);
			}

			$cart_item_data = [
				'has_voucher' => $is_voucher,
				'appointment' => $appointment,
				'appointment_date' => $date,
				'appointment_time' => $hourminutes,
				'aquos_id' => $aquos_id,
				//'timestamp_added' => time(),
				'virtual' => 1,
				'_virtual' => 1,
				//'price' => 12 // if I want to force a price in the cart
			];

			$return = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_data, $cart_item_data);

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

			wc_add_notice( sprintf( __( 'Your appointment was added. <a href="%s">Do you want to add another one?</a>', 'tmsm-aquos-spa-booking' ), wc_get_raw_referer() ? wp_validate_redirect( wc_get_raw_referer(), false ) : wc_get_page_permalink( 'shop' ) ) );

		}

		if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
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
	 * Send a response to ajax request, as JSON.
	 *
	 * @param mixed $response
	 */
	private function ajax_return( $response = true ) {
		echo json_encode( $response );
		exit;
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

			// We don't want non-priorities
			$diff_priorities = 0;

			$selected_times = $priorities;


			if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
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

				// Get random numbers inside not priorities
				$range_random_numbers = range( 0, ( $count_notpriorities - 1 ) );
				shuffle($range_random_numbers);
				$random_numbers = array_slice($range_random_numbers, 0 , $diff_priorities);

				foreach($random_numbers as $random_number){
					$selected_times[] = $not_priorities[$random_number];
				}

			}
			else{
				// Select only priorities, do nothing more
			}
		}

		// Sort by hour+minutes
		usort($selected_times, function($a, $b) {
			return strnatcmp($a['hourminutes'], $b['hourminutes']);
		});

		return $selected_times;
	}

	/**
	 * Validates recipient data before adding to cart
	 *
	 * @param bool    $valid
	 * @param integer $product_id
	 * @param integer $quantity
	 * @param integer $variation_id
	 * @param array   $variations
	 * @param array   $cart_item_data
	 *
	 * @return bool $valid
	 *@since 1.0.0
	 *
	 */
	public function woocommerce_add_to_cart_validation(	bool $valid, int $product_id, int $quantity, $variation_id = '', array $variations = array(), array $cart_item_data = array()
	): bool {
		global $wp;

		$product = wc_get_product($product_id);
		$variation = wc_get_product($variation_id);
		$product_to_check = $variation ?? $product;

		// Get Aquos price sum
		$product_to_check_aquosprice = get_post_meta( $product_to_check->get_id(), '_aquos_price', true );
		$product_to_check_aquosprice_values   = explode( '+', $product_to_check_aquosprice );
		$product_to_check_aquosprice_sum   = array_sum($product_to_check_aquosprice_values) ;

		//error_log('woocommerce_add_to_cart_validation:');
		//error_log($product_id);
		//error_log(print_r($cart_item_data, true));
		//error_log(print_r($variations, true));
		//error_log(print_r($product->get_price(), true));
		//error_log('$product_to_check->get_price(): ' .(float)$product_to_check->get_price());
		//error_log(print_r($product_to_check_aquosprice, true));
		//error_log(print_r($product_to_check_aquosprice_values, true));
		//error_log('$product_to_check_aquosprice_sum: ' . (float)$product_to_check_aquosprice_sum);


		// Check aquos price match product/variation price
		if ( (float)$product_to_check_aquosprice_sum !== (float)$product_to_check->get_price() ) {
			wc_add_notice( __( 'There is an error with the price configuration of this product, technical team was notified about this.', 'tmsm-aquos-spa-booking' ), 'error' );
			$valid = false;

			// Notify admin of issue
			$blogname = esc_html( get_bloginfo( 'name' ) );
			$email    = stripslashes( get_option( 'admin_email' ) );
			$subject  = sprintf(__( '%s: TMSM Aquos Spa Booking price/id not matching for variation/product %s', 'tmsm-aquos-spa-booking' ), $blogname, $product_to_check->get_id() );

			$message = '';
			$message .= '<br>product name: ' . $product_to_check->get_name();
			$message .= '<br>variation_id: ' . $variation_id;
			$message .= '<br>product_id: ' . $product_id;
			$message .= '<br>aquosprice sum: ' . (float)$product_to_check_aquosprice_sum;
			$message .= '<br>product/variation price: ' . (float)$product_to_check->get_price();
			$current_user = wp_get_current_user();
			if($current_user){
				$message .= '<br>user: ' . $current_user->user_email . ' - ' . $current_user->display_name;
			}
			$message .= '<br>requested url: ' .home_url( $wp->request );

			$headers = [
				'Auto-Submitted: auto-generated',
				'Content-Type: text/html',
				'Charset=UTF-8'
			];
			$email_sent = wp_mail( $email, $subject, $message, $headers );
		}

		// Check if aquos id exists
		$product_to_check_aquosid = get_post_meta( $product_to_check->get_id(), '_aquos_id', true );
		if ( empty($product_to_check_aquosid)) {
			wc_add_notice( __( 'There is an error with the IDs configuration of this product, technical team was notified about this.', 'tmsm-aquos-spa-booking' ), 'error' );
			$valid = false;

			// Notify admin
			$blogname = esc_html( get_bloginfo( 'name' ) );
			$email    = stripslashes( get_option( 'admin_email' ) );
			$subject  = sprintf(__( '%s: TMSM Aquos Spa Booking price/id missing for variation/product %s', 'tmsm-aquos-spa-booking' ), $blogname, $product_to_check->get_id() );

			$message = '';
			$message .= '<br>name: ' . $product_to_check->get_name();
			$message .= '<br>variation_id:' . $variation_id;
			$message .= '<br>product_id:' . $product_id;

			$headers = [
				'Auto-Submitted: auto-generated',
				'Content-Type: text/html',
				'Charset=UTF-8'
			];
			$email_sent = wp_mail( $email, $subject, $message, $headers );

		}

		return $valid;
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

		// For regular products with Aquos ID
		if(empty($cart_item_data['appointment'])){
			$cart_item_data['aquos_id'] = get_post_meta( $variation_id ?? $product_id, '_aquos_id', true );
			$cart_item_data['aquos_price'] = get_post_meta( $variation_id ?? $product_id, '_aquos_price', true );
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

		if ( !empty( $cart_item['aquos_price'] ) ) {
			$item_data[] = array(
				'key'     => __( 'Aquos Price', 'tmsm-aquos-spa-booking' ),
				'value'   => wc_clean( $cart_item['aquos_price'] ),
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

		if ( ! empty( $values['appointment'] ) ) { // Products with appointment
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
		else{ // Regular products
			if(!empty($values['aquos_id'])){
				$item->add_meta_data( '_aquos_id', $values['aquos_id'], true );
			}
			if(!empty($values['aquos_price'])){
				$item->add_meta_data( '_aquos_price', $values['aquos_price'], true );
			}
			if(empty($values['aquos_price']) && empty($values['aquos_id'])){
				$item->add_meta_data( '_aquos_error', 'id and price not defined', true );


				$blogname = esc_html( get_bloginfo( 'name' ) );
				$email    = stripslashes( get_option( 'admin_email' ) );
				$subject  = sprintf(__( '%s: TMSM Aquos Spa Booking price/id missing for order %s', 'tmsm-aquos-spa-booking' ), $blogname, $order->get_id());

				$message = '';
				$message .= 'item:' . print_r( $item, true );
				$message .= 'values:' . print_r( $values, true );

				$headers = [
					'Auto-Submitted: auto-generated',
					'Content-Type: text/html',
					'Charset=UTF-8'
				];
				$email_sent = wp_mail( $email, $subject, $message, $headers );

			}

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

		if ( isset($item['_has_voucher'])) {
			$strings[]           = '<strong class="wc-item-meta-label">' . __( 'Has Voucher:', 'tmsm-aquos-spa-booking' ) . '</strong> '. ($item['_has_voucher'] == 1 ? __( 'Yes', 'tmsm-aquos-spa-booking' ) : __( 'No', 'tmsm-aquos-spa-booking' ) );
		}

		//if ( isset($item['_appointment_processed'])) {
		//	$strings[]           = '<strong class="wc-item-meta-label">' . __( 'Appointment Processed:', 'tmsm-aquos-spa-booking' ) . '</strong> '. ($item['_appointment_processed'] === 'yes' ? __( 'Yes', 'tmsm-aquos-spa-booking' ) : __( 'No', 'tmsm-aquos-spa-booking' ) );
		//}

		if ( isset($item['_appointment_error']) && $item['_appointment_error'] === 'yes') {
			$strings[]           = '<strong class="wc-item-meta-label" style="color:#ca4444">' . __( 'Appointment has an error', 'tmsm-aquos-spa-booking' ) . '</strong>' ;
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

	public function woocommerce_checkout_before_order_review_heading() {
		global $woocommerce;

		if ( self::cart_has_appointmentonly() ) {
		?>
		<h3 id="order_review_heading"><?php esc_html_e( 'Your appointments', 'tmsm-aquos-spa-booking' ); ?></h3>
		<?php
		}
	}

	/**
	 * Check if Cart needs Shipping
	 *
	 * @param bool $needs_shipping
	 *
	 * @return bool
	 */
	function woocommerce_cart_needs_shipping(bool $needs_shipping){

		if(self::cart_has_appointment()){
			$needs_shipping = false;
		}
		return $needs_shipping;
	}

	/**
	 * Disable Other Payments Gateways if Cas On Delivery is Prefered Method
	 *
	 * @param $available_gateways
	 *
	 * @return mixed
	 */
	function woocommerce_available_payment_gateways_cashondelivery( $available_gateways ) {

		if(! empty(WC()->cart)) {

			$settings_acceptcashondelivery = get_option( 'tmsm_aquos_spa_booking_acceptcashondelivery', 'yes' );
			$settings_acceptonlinepayment  = get_option( 'tmsm_aquos_spa_booking_acceptonlinepayment', 'no' );

			// Check cart content: if all products are appointments
			$all_appointments = true;

			$order_id = absint( get_query_var( 'order-pay' ) );

			// Checking if we are on the order-pay page, we can parse the order
			if ( ! empty( $order_id ) ) {
				$appointmentsonly = $this->order_has_appointmentonly( $order_id );
			} // if not, we are on the checkout page, we can parse the cart
			else {
				$appointmentsonly = self::cart_has_appointmentonly();
			}

			// All products are appointments, allow accepted methods
			if ( $appointmentsonly === true ) {

				if ( $settings_acceptcashondelivery === 'no' ) {
					unset( $available_gateways['cod'] );
					unset( $available_gateways['paymentonsite'] );
				}
				if ( $settings_acceptonlinepayment === 'no' && $settings_acceptcashondelivery === 'yes' ) {
					if ( ! empty( $available_gateways ) ) {
						foreach ( $available_gateways as $available_gateway_key => $available_gateway ) {
							if ( ! in_array( $available_gateway_key, [ 'cod', 'paymentonsite' ] ) ) {
								unset( $available_gateways[ $available_gateway_key ] );
							}
						}
					}
				}
			} // If at least one product is not an appointment, then remove cod
			else {

				// If products are virtual, do not allow delivery or on site payments
				if ( ! WC()->cart->needs_shipping() ) {
					unset( $available_gateways['cod'] );
					unset( $available_gateways['paymentonsite'] );
				}
			}
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
	 * If Order has appointments only
	 *
	 * @param WC_Order|int $order
	 *
	 * @return bool
	 */
	private function order_has_appointmentonly($order){

		$order_id = WC_Order_Factory::get_order_id( $order );

		$order = wc_get_order($order_id);

		$appointmentonly = true;

		if ( ! empty( $order ) ) {

			foreach ( $order->get_items() as $order_item_id => $order_item_data) {

				// Has appointment
				if(empty($order_item_data['_appointment'])){
					$appointmentonly = false;
				}

			}
		}
		return $appointmentonly;
	}

	/**
	 * If Cart has at least one appointment
	 *
	 * @return bool
	 */
	private static function cart_has_appointment(){

		$count = 0;

		if(class_exists( 'woocommerce' )){
			if(is_admin()){
				return false;
			}

			if(empty(WC()->cart)){
				return false;
			}

			$cart_items = WC()->cart->get_cart_contents();


			foreach($cart_items as $cart_item){
				if(!empty($cart_item['appointment'])){
					$count ++;
				}
			}
		}

		return ($count > 0);
	}

	/**
	 * If Cart has appointments only
	 *
	 * @return bool
	 */
	private static function cart_has_appointmentonly(){

		//return true; // for tests

		if(empty(WC()->cart)){
			return false;
		}

		$cart_items = WC()->cart->get_cart_contents();

		$appointmentonly = true;
		if ( count( $cart_items ) === 0 ) {
			$appointmentonly = false;
		}
		foreach ( $cart_items as $key => $values ) {
			if(empty($values['appointment'])){
				$appointmentonly = false;
			}
		}

		return $appointmentonly;
	}

	/**
	 * If Cart has at least one appointment
	 *
	 * @return bool
	 */
	private static function cart_has_atleastonevoucher(){

		if ( ! empty( WC()->cart ) ) {

			$cart_items = WC()->cart->get_cart_contents();

			$count = 0;
			foreach($cart_items as $cart_item){
				if(!empty($cart_item['has_voucher'])){
					$count ++;
				}
			}

		}

		return ($count > 0);
	}

	/**
	 * If Cart has at least one appointment
	 *
	 * @return bool
	 */
	private static function cart_has_voucheronly(){

		$count = 0;

		if ( ! empty( WC()->cart ) ) {
			$cart_items = WC()->cart->get_cart_contents();


			foreach($cart_items as $cart_item){
				if(!empty($cart_item['has_voucher'])){
					$count ++;
				}
			}
		}


		return ($count == count( $cart_items ));
	}

	/**
	 * @param $thankyou
	 * @param $order WC_Order
	 *
	 * @return string
	 */
	function woocommerce_thankyou_order_received_text_appointment( $thankyou, $order ) {

		if ( ! empty( WC()->cart ) ) {
			WC()->cart->empty_cart();

			if ( self::order_has_appointment( $order ) === true ) {
				$message = get_option( 'tmsm_aquos_spa_booking_thankyou', false );

				if ( ! empty( $message ) ) {
					$thankyou .= '<br><br>' . nl2br(esc_html( $message ));
				}

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
				echo '<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">'.__( 'Submission failed, booking service has been notified and will contact you shortly.', 'tmsm-aquos-spa-booking' ).'</p>';
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
				echo '<p>' . nl2br(esc_html( $message )) . '</p>';
			}

			if ( $plain_text || ! is_a( $order, 'WC_Order' ) ) {
				return;
			}

			// Prepare data for markup
			$image = null;
			$address = null;
			$contact_page_id = null;
			if(class_exists('WPSEO_Options') && !empty(WPSEO_Options::get( 'company_logo' ))){
				$image = WPSEO_Options::get( 'company_logo' );
			}
			if(class_exists('RankMath\Helper')){
				$image = RankMath\Helper::get_settings( 'titles.knowledgegraph_logo' );
				$address = RankMath\Helper::get_settings( 'titles.local_address' );
				$contact_page_id = RankMath\Helper::get_settings( 'titles.local_seo_contact_page' );
			}

			$shop_name      = get_bloginfo( 'name' );
			$shop_url       = home_url();

			$markup = array();
			foreach ( $order->get_items() as $item ) {
				if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
					continue;
				}

				// Has appointment
				if ( empty( $item['_appointment'] ) ) {
					continue;
				}
				// Generate markup for every Event/Appointment
				$markup[] = array(
					'@context'          => 'http://schema.org',
					'@type'             => 'EventReservation',
					'reservationNumber' => $order->get_id(),
					'reservationStatus' => 'http://schema.org/Confirmed',
					'underName'         => [
						'@type' => 'Person',
						'name'  => $order->get_formatted_billing_full_name(),
					],
					'modifiedTime' => date(DATE_ATOM, time()),
					'modifyReservationUrl' => $contact_page_id ? get_permalink($contact_page_id) : '',
					//'modifyReservationUrl' => 'https://www.aquatonic.fr/nantes/contact/',
					//'modifyReservationUrl' => 'https://www.aquatonic.fr/rennes/contact/',
					//'modifyReservationUrl' => 'https://www.aquatonic.fr/paris/contact/',
					'reservationFor'    => [
						'@type'     => 'Event',
						'name'      => $item['name'],
						'performer' => [
							'@type' => 'Organization',
							'name'  => $shop_name,
							'image' => $image ?? '',
							//'image' => 'https://www.aquatonic.fr/nantes/wp-content/uploads/sites/8/2010/08/aquatonic-nantes-1.jpg',
							//'image' => 'https://mk0aquatonicxmkh2brf.kinstacdn.com/wp-content/uploads/sites/6/2017/08/aquatonic-rennes-1.jpg',
							//'image' => 'https://mk0aquatonicxmkh2brf.kinstacdn.com/wp-content/uploads/sites/9/2012/10/parcours-aquatonic-montevrain.png',


							//https://www.aquatonic.fr/nantes/wp-content/uploads/sites/8/2017/11/logo_aquatonic-nantes-600-300.png
							//https://www.aquatonic.fr/rennes/wp-content/uploads/sites/6/2017/11/logo_aquatonic-rennes-600-300.png
							//https://www.aquatonic.fr/paris/wp-content/uploads/sites/9/2017/11/logo_aquatonic-paris-600-300.png
						],
						'startDate' => $item['_appointment_date'] . 'T' . $item['_appointment_time'] . ':00',
						'location'  => [
							'@type'   => 'Place',
							'name'    => $shop_name,
							'address' => [
								'@type'           => 'PostalAddress',
								'streetAddress'   => ( $address ? $address['streetAddress'] : '' ),
								'addressLocality' => ( $address ? $address['addressLocality'] : '' ),
								'addressRegion'   => ( $address ? $address['addressRegion'] : '' ),
								'postalCode'      => ( $address ? $address['postalCode'] : '' ),
								'addressCountry'  => ( $address ? $address['addressCountry'] : '' ),
							],
						],
					],

				);
			}

			if ( $markup ) {
				echo '<script type="application/ld+json">' . wc_esc_json( wp_json_encode( $markup ), true ) . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

	}

	/**
	 * Check cart items
	 */
	public function woocommerce_check_cart_items(){

		if( is_cart() || is_checkout() ) {

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				// Remove Appointments that are expired, too old = 2 hours
				if(!empty($cart_item['appointment']) && !empty($cart_item['timestamp_added'])){
					$_product = $cart_item['data'];
					if( time() > ( $cart_item['timestamp_added'] + 60 * get_option( 'tmsm_aquos_spa_booking_cartexpireminutes', 60 ))){
						WC()->cart->remove_cart_item( $cart_item_key );
						wc_add_notice( sprintf( __( 'The product %s has been removed from cart since it has expired. Please try to book it again.', 'tmsm-aquos-spa-booking' ), $_product->get_name() ), 'error' );

					}
				}

				// Remove appointments in cart if other products are present in the cart
				if ( self::cart_has_appointment() && ! self::cart_has_appointmentonly() && ! empty( $cart_item['appointment'] ) ) {
					WC()->cart->remove_cart_item( $cart_item_key );
					wc_add_notice( __( 'The cart can\'t have both appointments and products, appointments are now removed from the cart.',
						'tmsm-aquos-spa-booking' ), 'error' );
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

		if ( class_exists( 'woocommerce' ) && is_checkout() && self::cart_has_appointmentonly() && $id === intval( get_option( 'woocommerce_checkout_page_id' ) ) ) {
			$title = __( 'Appointment', 'tmsm-aquos-spa-booking' );
		}

		return $title;
	}

	/**
	 * Filters the WooCommerce page "order received" title.
	 *
	 * @param string $title The post title.
	 * @param string $endpoint
	 *
	 * @return string
	 */
	public function woocommerce_endpoint_order_received_title( $title, $endpoint ) {
		global $wp;

		$order_id  = absint( $wp->query_vars['order-received'] );

		if ( is_order_received_page() && get_the_ID() == intval( get_option( 'woocommerce_checkout_page_id' )) && $this->order_has_appointmentonly($order_id)) {
			$title = __( 'Appointment booked', 'tmsm-aquos-spa-booking' );
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

		if ( ! empty( $post ) && ! empty( $post->ID ) ) {
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
	 * No more than 1 appointment in the cart (for same appointment time and product)
	 *
	 * @param string $cart_item_key contains the id of the cart item.
	 * @param int    $quantity contains the quantity of the item.
	 * @param int    $old_quantity contains the original quantity of the item.
	 * @param WC_Cart $cart The current cart object
	 */
	function woocommerce_after_cart_item_quantity_update( $cart_item_key, $quantity, $old_quantity, $cart ){

		if( !empty($cart->cart_contents[ $cart_item_key ]['appointment'])){
			$cart->cart_contents[ $cart_item_key ]['quantity'] = 1;
		}

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

		if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
			error_log('order '.$order_id.' order_status_appointment_voucher');
		}

		if ( self::order_has_appointment( $order ) ) {
			$status = 'wc-appointment';
		}

		return $status;
	}

	/**
	 * Do actions when order status changed to appointment
	 *
	 * @param int      $order_id
	 * @param WC_Order $order
	 *
	 */
	public function order_status_changed_to_appointment(int $order_id, WC_Order $order){
		if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
			error_log('order '.$order_id.' change_order_status_appointment');
		}

		if ( ! is_admin() ) {
			WC()->cart->empty_cart();
		}

		//$background_process = new Tmsm_Aquos_Spa_Booking_Background_Process();
		$background_process = $GLOBALS['tmsm_asb_bp'];

		$item = ['order_id' => $order_id];
		$background_process->push_to_queue( $item );

		$background_process->save()->dispatch();
	}


	/**
	 * Do actions when fee lines are added to order
	 *
	 * @param WC_Order_Item_Fee $item
	 * @param string $fee_key
	 * @param $fee
	 * @param WC_Order $order
	 */
	public function order_fee_item_metadata_giftwrap($item, $fee_key, $fee, $order){

		if ( $fee->name == __( 'Order gift wrap', 'tmsm-woocommerce-order-gift-wrap' ) ) {

			$aquos_id = get_option( 'tmsm_aquos_spa_booking_ordergiftwrapaquosid', '' );
			if ( ! empty( $aquos_id ) ) {
				$item->add_meta_data( '_aquos_id', $aquos_id, true );
			}

		}

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

		// Get "product_cat" Terms With Parent as an Option
		$settings_maincategory  = get_option( 'tmsm_aquos_spa_booking_productcat', 0 );
		$settings_excludedcategories  = get_option( 'tmsm_aquos_spa_booking_excludedproductcat', '' );
		$product_categories = get_terms( 'product_cat', [
			'hide_empty' => true,
			'exclude' => $settings_excludedcategories,
			'child_of' => !empty($settings_maincategory) ? $settings_maincategory: 0,
			'orderby'    => 'menu_order',
		]);

		return $product_categories;
	}

	/**
	 * Returns list of product attributes.
	 *
	 * @return array
	 */
	private function _get_product_attributes() {

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
		if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
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


					$variation_name = esc_js($variation->get_name(). (wc_get_formatted_variation($variation, true, false, true ) ? ' ㅡ '.wc_get_formatted_variation($variation, true, false, true ): '') );

					if($variation->get_attribute('format-bon-cadeau')){
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

		if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
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
		$products = [];

		$is_voucher = sanitize_text_field( ($_REQUEST['is_voucher'] ?? 0) );

		if( class_exists( 'woocommerce' ) ){
			$product_category_id = null;

			if(isset($_REQUEST['productcategory'])){
				$product_category_id = sanitize_text_field( $_REQUEST['productcategory'] );
			}

			// Order categories
			$product_categories = get_terms('product_cat' );
			$product_categories_order = [];
			foreach($product_categories as $product_category_index => $product_category){
				$product_categories_order[$product_category->name] = $product_category_index;
			}

			// Products arguments
			$args = array(
				'return'  => 'ids',
				'limit' => -1,
				'orderby' => 'name',
				'order' => 'ASC',
				'_bookable' => 'yes',
			);
			if(!$is_voucher){
				$args['status'] = 'publish';
			}

			if(!empty($product_category_id)){
				$product_category = get_term( $product_category_id, 'product_cat');
				$args['category'] = $product_category->slug;
			}
			else{
				$product_category = get_term( get_option( 'tmsm_aquos_spa_booking_productcat', 0 ), 'product_cat');
				$args['category'] = $product_category->slug;
			}

			//error_log('$args:');
			//error_log(print_r($args, true));

			// Find products
			$products_ids = wc_get_products( $args );
			if(!empty($products_ids)){
				foreach($products_ids as $key => $product_id){
					$product = wc_get_product($product_id);

					if( ($product->is_type( 'simple' ) && empty(get_post_meta( $product_id, '_aquos_id', true))) && ! get_post_meta( $product_id, '_aquos_items_ids', true) ){
						continue;
					}

					// Construct json data for product choices
					$aquos_items = [];
					if(!empty($product->get_meta( '_aquos_items_ids' ))){
						$aquos_items = preg_split('/\r\n|\r|\n/', esc_attr($product->get_meta( '_aquos_items_ids' )));
						foreach($aquos_items as &$aquos_item){

							$tmp_aquos_item = $aquos_item;
							$tmp_aquos_item_array = explode('*', $tmp_aquos_item);
							$aquos_item = [
								'name' => trim($tmp_aquos_item_array[0]),
								'aquos_id' => trim($tmp_aquos_item_array[1]),
							];

						}
					}



					$product_has_attributes_otherthan_voucher = false;

					// Don't display range price for variable products
					if($product->is_type( 'variable' ) && $product instanceof WC_Product_Variable) {

						// Not variable if only has voucher attribute
						if(is_array($product->get_attributes())){
							foreach($product->get_attributes() as $attribute_key => $attribute_value){
								if($attribute_key !== 'pa_format-bon-cadeau'){
									$product_has_attributes_otherthan_voucher = true;
								}
							}
						}

						$min_price_regular = $product->get_variation_regular_price( 'min', true );
						$min_price_sale    = $product->get_variation_sale_price( 'min', true );
						$max_price = $product->get_variation_price( 'max', true );
						$min_price = $product->get_variation_price( 'min', true );

						$price = ( $min_price_sale == $min_price_regular ) ? wc_price( $min_price_regular ) : wc_price( $min_price_sale ) ;
					}
					else{
						$price = $product->get_price_html();
					}


					// Get variation ID of voucher variation
					$product_has_only_attribute_voucher_variation_id = null;
					if($product_has_attributes_otherthan_voucher === false && $product->is_type( 'variable' )){
						$avail_vars = $product->get_available_variations();

						foreach ($avail_vars as $v){
							//error_log(print_r($v, true));
							if ($v["attributes"]["attribute_pa_format-bon-cadeau"] == 'e-bon-cadeau'){
								$product_has_only_attribute_voucher_variation_id = $v['variation_id'];
								//error_log('$product_has_only_attribute_voucher_variation_id: ' . $product_has_only_attribute_voucher_variation_id);
							}
						}
					}

					// Include product category, filter out excluded category
					$product_categories = get_the_terms($product->get_id() , 'product_cat' );
					$product_category_of_main_category_name = null;
					$product_category_of_main_category_id = null;

					// Find the sub category of main category
					foreach($product_categories as $product_category){
						if($product_category->parent ==  get_option( 'tmsm_aquos_spa_booking_productcat', 0 )){
							$product_category_of_main_category_name = $product_category->name;
							$product_category_of_main_category_id = $product_category->term_id;
						}
					}
					// Find if there is a sub category of sub category
					foreach($product_categories as $product_category){
						if($product_category->parent ==  $product_category_of_main_category_id){
							$product_category_of_main_category_name = $product_category->name;
							$product_category_of_main_category_id = $product_category->term_id;
						}
					}

					// Construct product data
					if($product_category_of_main_category_id != get_option( 'tmsm_aquos_spa_booking_excludedproductcat', 0 )){
						$products[] = [
							'id'                           => $product_has_only_attribute_voucher_variation_id ?? esc_js( $product->get_id() ),
							'permalink'                    => esc_js( $product->get_permalink() ),
							'thumbnail'                    => get_the_post_thumbnail_url( $product_id ) ? get_the_post_thumbnail_url( $product_id ) : '',
							'price'                        => html_entity_decode( wp_strip_all_tags( $price ) ),
							'sku'                          => esc_js( $product->get_sku() ),
							'name'                         => esc_js( $product->get_name() ),
							'variable'                     => esc_js( $product->is_type( 'variable' ) ),
							'voucher_variation_id'         => $product_has_only_attribute_voucher_variation_id,
							'attrotherthanvoucher' => esc_js( $product_has_attributes_otherthan_voucher ),
							'choices'                      => json_encode( $aquos_items ),
							'category'                     => esc_js( $product_category_of_main_category_name ),
							'category-index'               => $product_categories_order[ $product_category_of_main_category_name ],
						];
					}


				}
			}

			if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
				//error_log('$products:');
				//error_log(print_r($products, true));
			}
		}

		$products_column_category_index  = array_column($products, 'category-index');
		$products_column_name  = array_column($products, 'name');
		array_multisort($products_column_category_index, SORT_ASC, $products_column_name, SORT_ASC, $products);

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
		$productvariation_id = sanitize_text_field( $_REQUEST['productvariation'] );
		$choice_id           = sanitize_text_field( $_REQUEST['choice'] );
		$date                = sanitize_text_field( $_REQUEST['date'] );
		$date_with_dash      = $date;
		$times               = [];

		$product = wc_get_product( $product_id );

		// If it is a product
		$aquos_id = get_post_meta( $product_id, '_aquos_id', true );

		// If it is a variation
		if ( ! empty( $productvariation_id ) ) {
			$aquos_id = get_post_meta( $productvariation_id, '_aquos_id', true );
		}

		// If it is a choice
		if ( ! empty( $choice_id ) ) {
			$aquos_id = $choice_id;
		}
		$errors   = array(); // Array to hold validation errors
		$jsondata = array(); // Array to pass back data

		if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
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
		//if(empty( $product_category_id )){
		//	$errors[] = __( 'Product category is missing', 'tmsm-aquos-spa-booking' );
		//}

		if ( !empty( $errors ) ) {
			$jsondata['success'] = false;
			$jsondata['errors']  = $errors;
		}


		// Prepare web service data
		$settings_webserviceurl = get_option( 'tmsm_aquos_spa_booking_webserviceurltimes' );
		$settings_aquossiteid = get_option( 'tmsm_aquos_spa_booking_aquossiteid' );
		if ( ! empty( $settings_webserviceurl ) && isset( $settings_aquossiteid ) && ! empty( $aquos_id ) && ! empty( $date ) ) {

			if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
				error_log( 'url before:' . $settings_webserviceurl );
			}

			$aquos_id_array = explode('+', $aquos_id);
			$ignoredproducts = get_option( 'tmsm_aquos_spa_booking_ignoredproducts' );
			$ignoredproducts_array = explode(',', $ignoredproducts);

			if(count($ignoredproducts_array) > 0){
				$aquos_id_array = array_diff($aquos_id_array, $ignoredproducts_array);

				$aquos_id = implode('+',  $aquos_id_array );
			}
			$aquos_id_array_formatted = [];
			foreach($aquos_id_array as $aquos_id) {
				$aquos_id_array_formatted[] = ['id_product' => $aquos_id];
			}

			$data = [
				'id_site'       => $settings_aquossiteid,
				'date'          => esc_html( str_replace( '-', '', $date ) ),
				'list_products' => $aquos_id_array_formatted,
			];

			$body = json_encode($data);

			$headers = [
				'Content-Type' => 'application/json; charset=utf-8',
				'X-Signature' => $this->aquos_generate_signature( $body ),
				'Cache-Control' => 'no-cache',
			];

			$response = wp_remote_post(
				$settings_webserviceurl,
				array(
					'headers'     => $headers,
					'body'        => $body,
					'data_format' => 'body',
				)
			);
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			$errors = [];
			$logger = wc_get_logger();

			if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
				error_log( 'response_data' );
				error_log( print_r($response_data, true) );
			}

			if(empty($response)){
				$errors[] = __( 'Web service is not available', 'tmsm-aquos-spa-booking' );
				$logger->error(
					__( 'Web service is not available', 'tmsm-aquos-spa-booking' ),
					array(
						'source' => 'tmsm-aquos-spa-booking',
					)
				);
			}
			else{

				if ( $response_code >= 400 ) {
					error_log( sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquos-spa-booking' ), absint( $response_code ) ) );
					$errors[] = sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquos-spa-booking' ), absint( $response_code ) );
					$logger->error(
						sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquos-spa-booking' ), absint( $response_code ) ),
						array(
							'source' => 'tmsm-aquos-spa-booking',
						)
					);
				}

				if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
					error_log( 'response_data' );
					error_log( print_r($response_data, true) );
				}

				if(!empty($response_data->Status) && $response_data->Status == 'true'){

					foreach($response_data->Schedules as $schedule){
						$schedule_hourminutes = explode(':', $schedule->Hour);
						$times[] = [
						'date' => $date_with_dash,
						'hour' => $schedule_hourminutes[0],
						'minutes' => $schedule_hourminutes[1],
						'hourminutes' => $schedule->Hour,
						'priority' => $schedule->Priority,
						];
					}
				}
				else{
					if(!empty($response_data->ErrorCode) && !empty($response_data->ErrorMessage)){
						$errors[] = sprintf(__( 'Error code %s: %s', 'tmsm-aquos-spa-booking' ), $response_data->ErrorCode, $response_data->ErrorMessage);
						$logger->error(
							sprintf(__( 'Error code %s: %s', 'tmsm-aquos-spa-booking' ), $response_data->ErrorCode, $response_data->ErrorMessage),
							array(
								'source' => 'tmsm-aquos-spa-booking',
							)
						);
					}
				}
			}
		}

		//$times = self::sort_times_by_priority( $times );
		$times = self::select_times( $times );
		//$times = self::sort_times_by_hourminutes( $times );

		if ( count( $times ) == 0 ) {
			$errors[] = __( 'No time slot available for this day and this product', 'tmsm-aquos-spa-booking' );
			$times[] = [
				'date' => $date_with_dash,
				'hour' => null,
				'minutes' => null,
				'hourminutes' => null,
				'priority' => null,
			];
		}

		//$jsondata['times'] = $times;

		//$jsondata['success'] = true;
		//$jsondata['errors'] = $errors;

		if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
			error_log('$errors:');
			error_log(print_r($errors, true));
			error_log('$times:');
			error_log(print_r($times, true));
		}

		return $times;
	}

	/**
	 * Aquos: generate signature
	 *
	 * @param string $payload
	 *
	 * @return string
	 */
	private function aquos_generate_signature( $payload ) {
		$hash_algo = 'sha256';

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( hash_hmac( $hash_algo, $payload, wp_specialchars_decode( $this->aquos_secret(), ENT_QUOTES ), true ) );
	}

	/**
	 * Aquos: returns secret
	 *
	 * @return string
	 */
	private function aquos_secret() {

		$secret = get_option('tmsm_aquos_spa_booking_aquossecret');

		return $secret;
	}

	/**
	 * Rename phone field description
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function billing_fields_phone( $fields ) {

		if(self::cart_has_appointment()){
			$fields['billing_phone']['description']  = __( 'You will receive a text message two days before the appointment.', 'tmsm-aquos-spa-booking' );
		}

		return $fields;
	}

	/**
	 * Update order item's meta aquos_price if product was discounted with a coupon
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order_Item_Product $item
	 * @param string                $cart_item_key
	 * @param array                 $values
	 * @param WC_Order              $order
	 */
	public function create_order_line_item_with_coupon( $item, $cart_item_key, $values, $order ) {

		// Product was discounted with a coupon
		if($item->get_total() != $item->get_subtotal() && $item->get_subtotal() > $item->get_total() ){

			$discount = 100 - ( $item->get_total() * 100 / $item->get_subtotal() );
			if($discount > 0 && $discount < 100){
				$aquos_prices = $item->get_meta('_aquos_price', true);

				// Store old aquos price as item meta
				$item->add_meta_data( '_aquos_price_regular', $aquos_prices, true );

				$aquos_prices_array = explode('+', $aquos_prices);

				if(is_array($aquos_prices_array)){
					$aquos_prices_array_new = [];
					foreach ($aquos_prices_array as $aquos_price){
						$aquos_prices_array_new[] = $aquos_price - ( $aquos_price * $discount / 100 );
					}

					$aquos_prices_new = join('+', $aquos_prices_array_new);

					// Store new aquos price and replace old price
					$aquos_prices = $item->update_meta_data('_aquos_price', $aquos_prices_new);
				}

			}

		}

	}
}
