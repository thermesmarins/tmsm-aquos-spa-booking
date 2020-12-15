<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/thermesmarins/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/includes
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'WC_Settings_Aquosspabooking' ) ) :
		/**
		 * Settings class
		 *
		 * @since 1.0.0
		 */
		class WC_Settings_Aquosspabooking extends WC_Settings_Page {

			/**
			 * Setup settings class
			 *
			 * @since  1.0
			 */
			public function __construct() {

				$this->id    = 'tmsmaquosspabooking';
				$this->label = __( 'Aquos Spa Booking', 'tmsm-aquos-spa-booking' );

				add_filter( 'woocommerce_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
				add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
				add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
				add_action( 'woocommerce_sections_' . $this->id,      array( $this, 'output_sections' ) );
			}

			/**
			 * Get sections
			 *
			 * @return array
			 */
			public function get_sections() {

				$sections = array(
					''                 => __( 'Settings', 'tmsm-aquos-spa-booking' ),
					'info'             => __( 'Info', 'tmsm-aquos-spa-booking' ),
					'regenerateprices' => __( 'Regenerates Aquos Prices', 'tmsm-aquos-spa-booking' ),
				);

				return $sections;
			}

			/**
			 * Get settings array
			 *
			 * @since 1.0.0
			 * @param string $current_section Optional. Defaults to empty string.
			 * @return array Array of settings
			 */
			public function get_settings( $current_section = '' ) {

				$meta_key_aquos_price = '_aquos_price';

				$settings = [];

				if($current_section == 'regenerateprices'){
					global $wpdb;

					echo "<br>";
					echo __( 'This section finds if Aquos IDs are missing for the web service synchronization.', 'tmsm-aquos-spa-booking' );
					echo "<br>";
					$products = wc_get_products(['numberposts' => -1, 'post_status' => 'publish']);
					foreach($products as $product){




						if ($product->get_type() == "variable") {
							foreach ($product->get_available_variations() as $variation) {

									$aquos_id = get_post_meta($variation['variation_id'], '_aquos_id', true);

									if(empty($aquos_id)){
										echo $product->get_title() . " - ".$variation['sku'];
										echo ' variation ID '.$product->get_id().' / aquos_id missing';
									}

							}
						}
						else{
							$aquos_id = get_post_meta($product->get_id(), '_aquos_id', true);
							if(empty($aquos_id)){
								echo "<br>";
								echo $product->get_name();
								echo ' product ID '.$product->get_id().' / aquos_id missing';
							}
						}

					}




					echo "<br>";
					echo "<br>";
					echo "<br>";
					echo __( 'This page regenerates the Aquos Price Meta Data needed for the web service synchronization.', 'tmsm-aquos-spa-booking' );
					echo "<br>";

					$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => $meta_key_aquos_price ] );

					$results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = '_aquos_id' AND meta_value != '' ");

					if(!empty($results)){
						$count_meta = count( $results );
						$errors_nb = 0;

						echo sprintf(__( 'Parsing %d products', 'tmsm-aquos-spa-booking' ), $count_meta);
						echo "<br>";
						echo "<br>";

						foreach ( $results as $result ) {

							echo 'product id '.$result->post_id . ' / ';

							$aquos_price = [];
							$aquos_ids   = explode( '+', $result->meta_value );
							foreach ( $aquos_ids as $aquos_id ) {
								$price = self::get_product_price_from_aquos_or_product_id( $aquos_id, $result->post_id );
								$aquos_price[] = $price;
								if($price == 0){
									echo sprintf(__( 'Price not found for product %s', 'tmsm-aquos-spa-booking' ), $result->post_id);
									echo ' / ';
								}
							}
							$result->aquos_price = join( '+', $aquos_price );
							echo 'detailed price '.$result->aquos_price . ' / ';

							// Checking if calculated price matches product price
							$product = wc_get_product($result->post_id);
							$product_price = 0;
							if(!empty($product)){
								$product_price = $product->get_price();
							}
							$calculated_price = 0;
							foreach($aquos_price as $item_price){
								$calculated_price+=$item_price;
							}
							echo 'calculated price '.$calculated_price . ' / ';

							if($calculated_price != $product_price){
								echo '<b>NON MATCHING PRICE with '.$product_price . '</b> / ';

								if(!empty($product)){
									echo 'product is '. $product->get_status() . ' / ';
									$parent = wc_get_product($product->get_parent_id());
									if(!empty($parent)){
										echo 'variation is '.  $parent->get_status(). ' / ';
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
							echo "<br>";
							echo "<br>";
						}

						echo sprintf(__( '%d matching price errors', 'tmsm-aquos-spa-booking' ), $errors_nb);
					}

				}
				elseif($current_section == 'info'){

					include( plugin_dir_path( dirname( __FILE__ ) ) .'admin/partials/tmsm-aquos-spa-booking-admin-display.php' );
				}
				else{
					$pages = [];
					$pages[''] = __( 'Select a contact page', 'tmsm-aquos-spa-booking' );
					foreach(get_pages(['sort_column' => 'post_title', 'hierarchical' => false ]) as $page){
						$pages[$page->ID] = $page->post_title;
					}

					$settings = array(

						array(
							'name' => __( 'Aquos Spa Booking Settings', 'tmsm-aquos-spa-booking' ),
							'type' => 'title',
							'desc' => '',
							'id'   => 'tmsm_aquos_spa_booking_settings',
						),

						array(
							'type'     => 'url',
							'id'       => 'tmsm_aquos_spa_booking_webserviceurltimes',
							'name'     => __( 'Web Service URL for times', 'tmsm-aquos-spa-booking' ),
							'desc'     => ''.
							              '<p class="description">'.
							              __( 'The URL of the Aquos web service. Use the special variables in the URL:', 'tmsm-aquos-spa-booking').
							              '<br>'.sprintf(__( '%s as the the web site id.', 'tmsm-aquos-spa-booking'), '<code>{site_id}</code>').
							              '<br>'.sprintf(__( '%s as the requested product ID.', 'tmsm-aquos-spa-booking'), '<code>{product_id}</code>').
							              '<br>'.sprintf(__( '%s as the requested date.', 'tmsm-aquos-spa-booking'), '<code>{date}</code>').
							              '<br>'.sprintf(__( '%s as the web site name.', 'tmsm-aquos-spa-booking'), '<code>{site_name}</code>').
							              '</p>',
							'class'  => 'large-text',
						),

						array(
							'type'     => 'url',
							'id'       => 'tmsm_aquos_spa_booking_webserviceurlsubmit',
							'name'     => __( 'Web Service URL for submission', 'tmsm-aquos-spa-booking' ),
							'desc'     => ''.
							              '<p class="description">'.
							              __( 'The URL of the Aquos web service. Use the special variables in the URL:', 'tmsm-aquos-spa-booking').
							              '<br>'.sprintf(__( '%s as the the web site id.', 'tmsm-aquos-spa-booking'), '<code>{site_id}</code>').
							              '<br>'.sprintf(__( '%s as the requested product ID.', 'tmsm-aquos-spa-booking'), '<code>{product_id}</code>').
							              '<br>'.sprintf(__( '%s as the requested date.', 'tmsm-aquos-spa-booking'), '<code>{date}</code>').
							              '<br>'.sprintf(__( '%s as the requested time.', 'tmsm-aquos-spa-booking'), '<code>{time}</code>').
							              '<br>'.sprintf(__( '%s as the customer title.', 'tmsm-aquos-spa-booking'), '<code>{title}</code>').
							              '<br>'.sprintf(__( '%s as the customer lastname.', 'tmsm-aquos-spa-booking'), '<code>{lastname}</code>').
							              '<br>'.sprintf(__( '%s as the customer firstname.', 'tmsm-aquos-spa-booking'), '<code>{firstname}</code>').
							              '<br>'.sprintf(__( '%s as the customer email.', 'tmsm-aquos-spa-booking'), '<code>{email}</code>').
							              '<br>'.sprintf(__( '%s if customer has a voucher.', 'tmsm-aquos-spa-booking'), '<code>{has_voucher}</code>').
							              '<br>'.sprintf(__( '%s as the customer birthdate.', 'tmsm-aquos-spa-booking'), '<code>{birthdate}</code>').
							              '<br>'.sprintf(__( '%s as the customer address.', 'tmsm-aquos-spa-booking'), '<code>{address}</code>').
							              '<br>'.sprintf(__( '%s as the customer postalcode.', 'tmsm-aquos-spa-booking'), '<code>{postalcode}</code>').
							              '<br>'.sprintf(__( '%s as the customer city.', 'tmsm-aquos-spa-booking'), '<code>{city}</code>').
							              '<br>'.sprintf(__( '%s as the customer phone.', 'tmsm-aquos-spa-booking'), '<code>{phone}</code>').
							              '<br>'.sprintf(__( '%s as the customer notes.', 'tmsm-aquos-spa-booking'), '<code>{notes}</code>').
							              '<br>'.sprintf(__( '%s as the web site name.', 'tmsm-aquos-spa-booking'), '<code>{site_name}</code>').
							              '</p>',
							'class'  => 'large-text',
						),

						array(
							'type'     => 'number',
							'id'       => 'tmsm_aquos_spa_booking_productcat',
							'name'     => __( 'WooCommerce Product Category ID', 'tmsm-aquos-spa-booking' ),
							'desc'     => ''.
							              '<p class="description">'.
							              __( 'The ID of the product category. Leave 0 if all categories can be booked.', 'tmsm-aquos-spa-booking'). '</p>',
							'default' => '',
							'class'  => 'regular-text',
						),

						array(
							'type'     => 'number',
							'id'       => 'tmsm_aquos_spa_booking_excludedproductcat',
							'name'     => __( 'WooCommerce Product Excluded Category IDs', 'tmsm-aquos-spa-booking' ),
							'desc'     => ''.
							              '<p class="description">'.
							              __( 'The IDs of excluded product categories. Separated by comma.', 'tmsm-aquos-spa-booking'). '</p>',
							'default' => '',
							'class'  => 'regular-text',
						),

						array(
							'type'     => 'number',
							'id'       => 'tmsm_aquos_spa_booking_ordergiftwrapaquosid',
							'name'     => __( 'Order Gift Wrap Aquos ID', 'tmsm-aquos-spa-booking' ),
							'desc'     => ''.
							              '<p class="description">'.
							              __( 'The ID of order gift wrap in Aquos', 'tmsm-aquos-spa-booking'). '</p>',
							'default' => '',
							'class'  => 'regular-text',
						),

						array(
							'type'     => 'text',
							'id'       => 'tmsm_aquos_spa_booking_ignoredproducts',
							'name'     => __( 'Aquos products ignored when booking', 'tmsm-aquos-spa-booking' ),
							'desc'     => ''.
							              '<p class="description">'.
							              __( 'Aquos product IDs separated by comma. Those products can’t be booked.', 'tmsm-aquos-spa-booking'). '</p>',
							'default' => 0,
							'class'  => 'regular-text',
						),

						array(
							'type'     => 'checkbox',
							'id'       => 'tmsm_aquos_spa_booking_acceptcashondelivery',
							'name'     => __( 'Accept Cash On Delivery', 'tmsm-aquos-spa-booking' ),
							'desc'     => '<p class="description">'.__( 'Accept Cash On Delivery for the bookable products. It has to be enabled in the Checkout Page. Note: if cart has other types of products, the default methods will apply.', 'tmsm-aquos-spa-booking'  ).'</p>',
							'default'  => 'yes',
						),

						array(
							'type'     => 'checkbox',
							'id'       => 'tmsm_aquos_spa_booking_acceptonlinepayment',
							'name'     => __( 'Accept Online Payment', 'tmsm-aquos-spa-booking' ),
							'desc'     => '<p class="description">'.__( 'Accept Online Payment for the Bookable Products.', 'tmsm-aquos-spa-booking'  ).'</p>',
							'default'  => 'no',
						),


						array(
							'type'     => 'select',
							'id'       => 'tmsm_aquos_spa_booking_contactpage',
							'name'     => __( 'Contact Page', 'tmsm-aquos-spa-booking' ),
							'default'  => 'no',
							'options' => $pages,
						),

						array(
							'type'     => 'select',
							'id'       => 'tmsm_aquos_spa_booking_dateselection',
							'name'     => __( 'Date Selection', 'tmsm-aquos-spa-booking' ),
							'default'  => 'calendar',
							'options' => [
								'calendar' => __( 'Calendar View', 'tmsm-aquos-spa-booking' ),
								'weekdays' => __( 'Week Days View', 'tmsm-aquos-spa-booking' ),
								],
						),

						array(
							'type'     => 'number',
							'id'       => 'tmsm_aquos_spa_booking_daysrangefrom',
							'name'     => __( 'Calendar Days Range From', 'tmsm-aquos-spa-booking' ),
							'desc'     => '<p class="description">'.__( 'Users can\'t book before this number of days after the current date.', 'tmsm-aquos-spa-booking'  ).'</p>',
							'default'  => 1,
						),

						array(
							'type'     => 'number',
							'id'       => 'tmsm_aquos_spa_booking_daysrangeto',
							'name'     => __( 'Calendar Days Range To', 'tmsm-aquos-spa-booking' ),
							'desc'     => '<p class="description">'.__( 'Users can\'t book after this number of days after the current date.', 'tmsm-aquos-spa-booking'  ).'</p>',
							'default'  => 60,
						),

						array(
							'type'     => 'text',
							'id'       => 'tmsm_aquos_spa_booking_datebeforeforbidden',
							'name'     => __( 'Calendar Days Before Forbidden', 'tmsm-aquos-spa-booking' ),
							'desc'     => '<p class="description">'.__( 'Users can\'t book before this date, format YYYY-MM-DD. Leave empty for no restriction', 'tmsm-aquos-spa-booking'  ).'</p>',
							'default'  => '',
						),

						array(
							'type'     => 'number',
							'id'       => 'tmsm_aquos_spa_booking_cartexpireminutes',
							'name'     => __( 'Cart Products Expires', 'tmsm-aquos-spa-booking' ),
							'desc'     => '<p class="description">'.__( 'Minutes', 'tmsm-aquos-spa-booking'  ).'</p>',
							'default'  => 60,
						),

						array(
							'type'     => 'textarea',
							'id'       => 'tmsm_aquos_spa_booking_cancellationpolicy',
							'name'     => __( 'Cancellation Policy', 'tmsm-aquos-spa-booking' ),
						),

						array(
							'type'     => 'textarea',
							'id'       => 'tmsm_aquos_spa_booking_thankyou',
							'name'     => __( 'Message in order received page', 'tmsm-aquos-spa-booking' ),
						),

						array(
							'type'     => 'textarea',
							'id'       => 'tmsm_aquos_spa_booking_orderemail',
							'name'     => __( 'Message in order email', 'tmsm-aquos-spa-booking' ),
						),

						array(
							'type' => 'sectionend',
							'id'   => 'tmsm_aquos_spa_booking_settings'
						),

					) ;
				}



				return $settings;

			}

			/**
			 * Output the settings
			 *
			 * @since 1.0
			 */
			public function output() {

				global $current_section;

				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::output_fields( $settings );
			}

			/**
			 * Save settings
			 *
			 * @since 1.0
			 */
			public function save() {

				global $current_section;

				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::save_fields( $settings );
			}

			/**
			 * Get Price From Aquos or Product ID
			 *
			 * @param int $aquos_id
			 * @param int $product_id
			 *
			 * @return int
			 */
			function get_product_price_from_aquos_or_product_id(int $aquos_id, $product_id){

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
						case 368: // Parcours Aquatonic (Paris)
							$price = 29;
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
					echo 'findind product with aquos id '.$aquos_id.' / ';

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
						echo 'product price variable '.$price . ' / ';
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
							echo 'finding product with post_id '.$result->post_id . ' / ';
							$product = wc_get_product($result->post_id);

							if(!empty($product)){

								$price = $product->get_price();
								echo 'product price '.$price . ' / ';

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
					echo 'hard coded price '.$price . ' for '.$aquos_id. ' / ';
				}

				return $price;
			}
		}


endif;
return new WC_Settings_Aquosspabooking();