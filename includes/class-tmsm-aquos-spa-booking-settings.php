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



if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!class_exists('WC_Settings_Aquosspabooking')) :
	/**
	 * Settings class
	 *
	 * @since 1.0.0
	 */
	class WC_Settings_Aquosspabooking extends WC_Settings_Page
	{

		/**
		 * Setup settings class
		 *
		 * @since  1.0
		 */
		public function __construct()
		{

			$this->id    = 'tmsmaquosspabooking';
			$this->label = __('Aquos Spa Booking', 'tmsm-aquos-spa-booking');

			add_filter('woocommerce_settings_tabs_array',        array($this, 'add_settings_page'), 20);
			add_action('woocommerce_settings_' . $this->id,      array($this, 'output'));
			add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
			add_action('woocommerce_sections_' . $this->id,      array($this, 'output_sections'));
		}

		/**
		 * Get sections
		 *
		 * @return array
		 */
		public function get_sections()
		{

			$sections = array(
				''                 => __('Settings', 'tmsm-aquos-spa-booking'),
				'info'             => __('Info', 'tmsm-aquos-spa-booking'),
				'regenerateprices' => __('Regenerates Aquos Prices', 'tmsm-aquos-spa-booking'),
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
		public function get_settings($current_section = '')
		{

			$settings = [];

			if ($current_section == 'regenerateprices') {

				echo '<br>';
				do_action('tmsm_aquos_spa_booking_cronaction', true);
			} elseif ($current_section == 'info') {

				include(plugin_dir_path(dirname(__FILE__)) . 'admin/partials/tmsm-aquos-spa-booking-admin-display.php');
			} else {
				$pages = [];
				$pages[''] = __('Select a page', 'tmsm-aquos-spa-booking');
				foreach (get_pages(['sort_column' => 'post_title', 'hierarchical' => false]) as $page) {
					$pages[$page->ID] = $page->post_title;
				}
				$currentDate = new \DateTime();
				$year = $currentDate->format('Y');

				$settings = array(

					array(
						'name' => __('Aquos Spa Booking Settings', 'tmsm-aquos-spa-booking'),
						'type' => 'title',
						'desc' => '',
						'id'   => 'tmsm_aquos_spa_booking_settings',
					),

					array(
						'type'     => 'number',
						'id'       => 'tmsm_aquos_spa_booking_aquossiteid',
						'name'     => __('Aquos Site ID', 'tmsm-aquos-spa-booking'),
						'class'  => 'small-text',
					),

					array(
						'type'     => 'text',
						'id'       => 'tmsm_aquos_spa_booking_aquossecret',
						'name'     => __('Aquos Secret', 'tmsm-aquos-spa-booking'),
						'class'  => 'small-text',
					),
					array(
						'type'     => 'number',
						'id'       => 'tmsm_aquos_spa_booking_daysbeforecancellation',
						'name'     => __('Days before cancellation', 'tmsm-aquos-spa-booking'),
						'desc'     => '' .
							'<p class="description">' .
							__('Days limit before cancelation', 'tmsm-aquos-spa-booking') . '</p>',
						'class'  => 'small-text',
					),
					array(
						'type'     => 'url',
						'id'       => 'tmsm_aquos_spa_booking_webserviceurldelete',
						'name'     => __('Web Service URL for deletion', 'tmsm-aquos-spa-booking'),
						'desc'     => '' .
							'<p class="description">' .
							__('The URL of the Aquos web service. Use the special variables in the URL:', 'tmsm-aquos-spa-booking') .
							'<br>' . sprintf(__('%s as the the web site id.', 'tmsm-aquos-spa-booking'), '<code>{site_id}</code>') .
							'<br>' . sprintf(__('%s as the requested appointment ID.', 'tmsm-aquos-spa-booking'), '<code>{appointment_id}</code>') .
							'</p>',
						'class'  => 'large-text',
					),
					array(
						'type'     => 'text',
						'id'       => 'tmsm_aquos_spa_booking_deleteaquossecret',
						'name'     => __('Aquos Secret Delete', 'tmsm-aquos-spa-booking'),
						'class'  => 'small-text',
					),
				
					array(
						'type'     => 'url',
						'id'       => 'tmsm_aquos_spa_booking_webserviceurltimes',
						'name'     => __('Web Service URL for times', 'tmsm-aquos-spa-booking'),
						'desc'     => '' .
							'<p class="description">' .
							__('The URL of the Aquos web service. Use the special variables in the URL:', 'tmsm-aquos-spa-booking') .
							'<br>' . sprintf(__('%s as the the web site id.', 'tmsm-aquos-spa-booking'), '<code>{site_id}</code>') .
							'<br>' . sprintf(__('%s as the requested product ID.', 'tmsm-aquos-spa-booking'), '<code>{product_id}</code>') .
							'<br>' . sprintf(__('%s as the requested date.', 'tmsm-aquos-spa-booking'), '<code>{date}</code>') .
							'<br>' . sprintf(__('%s as the web site name.', 'tmsm-aquos-spa-booking'), '<code>{site_name}</code>') .
							'</p>',
						'class'  => 'large-text',
					),

					array(
						'type'     => 'url',
						'id'       => 'tmsm_aquos_spa_booking_webserviceurlsubmit',
						'name'     => __('Web Service URL for submission', 'tmsm-aquos-spa-booking'),
						'desc'     => '' .
							'<p class="description">' .
							__('The URL of the Aquos web service. Use the special variables in the URL:', 'tmsm-aquos-spa-booking') .
							'<br>' . sprintf(__('%s as the the web site id.', 'tmsm-aquos-spa-booking'), '<code>{site_id}</code>') .
							'<br>' . sprintf(__('%s as the requested product ID.', 'tmsm-aquos-spa-booking'), '<code>{product_id}</code>') .
							'<br>' . sprintf(__('%s as the requested date.', 'tmsm-aquos-spa-booking'), '<code>{date}</code>') .
							'<br>' . sprintf(__('%s as the requested time.', 'tmsm-aquos-spa-booking'), '<code>{time}</code>') .
							'<br>' . sprintf(__('%s as the customer title.', 'tmsm-aquos-spa-booking'), '<code>{title}</code>') .
							'<br>' . sprintf(__('%s as the customer lastname.', 'tmsm-aquos-spa-booking'), '<code>{lastname}</code>') .
							'<br>' . sprintf(__('%s as the customer firstname.', 'tmsm-aquos-spa-booking'), '<code>{firstname}</code>') .
							'<br>' . sprintf(__('%s as the customer email.', 'tmsm-aquos-spa-booking'), '<code>{email}</code>') .
							'<br>' . sprintf(__('%s if customer has a voucher.', 'tmsm-aquos-spa-booking'), '<code>{has_voucher}</code>') .
							'<br>' . sprintf(__('%s as the customer birthdate.', 'tmsm-aquos-spa-booking'), '<code>{birthdate}</code>') .
							'<br>' . sprintf(__('%s as the customer address.', 'tmsm-aquos-spa-booking'), '<code>{address}</code>') .
							'<br>' . sprintf(__('%s as the customer postalcode.', 'tmsm-aquos-spa-booking'), '<code>{postalcode}</code>') .
							'<br>' . sprintf(__('%s as the customer city.', 'tmsm-aquos-spa-booking'), '<code>{city}</code>') .
							'<br>' . sprintf(__('%s as the customer phone.', 'tmsm-aquos-spa-booking'), '<code>{phone}</code>') .
							'<br>' . sprintf(__('%s as the customer notes.', 'tmsm-aquos-spa-booking'), '<code>{notes}</code>') .
							'<br>' . sprintf(__('%s as the web site name.', 'tmsm-aquos-spa-booking'), '<code>{site_name}</code>') .
							'</p>',
						'class'  => 'large-text',
					),

					array(
						'type'     => 'number',
						'id'       => 'tmsm_aquos_spa_booking_productcat',
						'name'     => __('WooCommerce Product Category ID', 'tmsm-aquos-spa-booking'),
						'desc'     => '' .
							'<p class="description">' .
							__('The ID of the product category. Leave 0 if all categories can be booked.', 'tmsm-aquos-spa-booking') . '</p>',
						'default' => '',
						'class'  => 'regular-text',
					),

					array(
						'type'     => 'number',
						'id'       => 'tmsm_aquos_spa_booking_excludedproductcat',
						'name'     => __('WooCommerce Product Excluded Category IDs', 'tmsm-aquos-spa-booking'),
						'desc'     => '' .
							'<p class="description">' .
							__('The IDs of excluded product categories. Separated by comma.', 'tmsm-aquos-spa-booking') . '</p>',
						'default' => '',
						'class'  => 'regular-text',
					),

					array(
						'type'     => 'number',
						'id'       => 'tmsm_aquos_spa_booking_ordergiftwrapaquosid',
						'name'     => __('Order Gift Wrap Aquos ID', 'tmsm-aquos-spa-booking'),
						'desc'     => '' .
							'<p class="description">' .
							__('The ID of order gift wrap in Aquos', 'tmsm-aquos-spa-booking') . '</p>',
						'default' => '',
						'class'  => 'regular-text',
					),

					array(
						'type'     => 'text',
						'id'       => 'tmsm_aquos_spa_booking_ignoredproducts',
						'name'     => __('Aquos products ignored when booking', 'tmsm-aquos-spa-booking'),
						'desc'     => '' .
							'<p class="description">' .
							__('Aquos product IDs separated by comma. Those products can’t be booked.', 'tmsm-aquos-spa-booking') . '</p>',
						'default' => 0,
						'class'  => 'regular-text',
					),

					array(
						'type'     => 'checkbox',
						'id'       => 'tmsm_aquos_spa_booking_acceptcashondelivery',
						'name'     => __('Accept Cash On Delivery', 'tmsm-aquos-spa-booking'),
						'desc'     => '<p class="description">' . __('Accept Cash On Delivery for the bookable products. It has to be enabled in the Checkout Page. Note: if cart has other types of products, the default methods will apply.', 'tmsm-aquos-spa-booking') . '</p>',
						'default'  => 'yes',
					),

					array(
						'type'     => 'checkbox',
						'id'       => 'tmsm_aquos_spa_booking_acceptonlinepayment',
						'name'     => __('Accept Online Payment', 'tmsm-aquos-spa-booking'),
						'desc'     => '<p class="description">' . __('Accept Online Payment for the Bookable Products.', 'tmsm-aquos-spa-booking') . '</p>',
						'default'  => 'no',
					),

					array(
						'type'     => 'checkbox',
						'id'       => 'tmsm_aquos_spa_booking_enablecourseintegration',
						'name'     => __('Enable Course Booking Integration', 'tmsm-aquos-spa-booking'),
						'default'  => 'no',
					),

					array(
						'type'     => 'select',
						'id'       => 'tmsm_aquos_spa_booking_contactpage',
						'name'     => __('Contact Page', 'tmsm-aquos-spa-booking'),
						'default'  => 'no',
						'options'  => $pages,
					),

					array(
						'type'     => 'select',
						'id'       => 'tmsm_aquos_spa_booking_bookingpage',
						'name'     => __('Booking Page (link from product single page)', 'tmsm-aquos-spa-booking'),
						'default'  => 'no',
						'options'  => $pages,
					),

					array(
						'type'    => 'select',
						'id'      => 'tmsm_aquos_spa_booking_dateselection',
						'name'    => __('Date Selection', 'tmsm-aquos-spa-booking'),
						'default' => 'calendar',
						'options' => [
							'calendar' => __('Calendar View', 'tmsm-aquos-spa-booking'),
							'weekdays' => __('Week Days View', 'tmsm-aquos-spa-booking'),
						],
					),

					array(
						'type'    => 'number',
						'id'      => 'tmsm_aquos_spa_booking_hoursrangefrom',
						'name'    => __('Calendar Hours Range From', 'tmsm-aquos-spa-booking'),
						'desc'    => '<p class="description">' . __(
							'Users can\'t book before this number of hours after the current datetime.',
							'tmsm-aquos-spa-booking'
						) . '</p>',
						'default' => 24,
					),

					array(
						'type'    => 'number',
						'id'      => 'tmsm_aquos_spa_booking_daysrangeto',
						'name'    => __('Calendar Days Range To', 'tmsm-aquos-spa-booking'),
						'desc'    => '<p class="description">' . __(
							'Users can\'t book after this number of days after the current date.',
							'tmsm-aquos-spa-booking'
						) . '</p>',
						'default' => 60,
					),
					array(
						'type'    => 'text',
						'id'      => 'tmsm_aquos_spa_booking_dateto',
						'name'    => __('Display tarif Change message to', 'tmsm-aquos-spa-booking'),
						'desc'    => '<p class="description">' . __(
							'Date end, format YYYY-MM-DD, to display the message last day include.',
							'tmsm-aquos-spa-booking'
						) . '</p>',
						'default' => "{$year}-31-12",
					),
					array(
						'type'    => 'text',
						'id'      => 'tmsm_aquos_spa_booking_messagestrong',
						'name'    => __('First paragraph of the message to display', 'tmsm-aquos-spa-booking'),
						'desc'    => '<p class="description">' . __(
							'Message to display for not having a gift vouncher',
							'tmsm-aquos-spa-booking'
						) . '</p>',
					),
					array(
						'type'    => 'textarea',
						'id'      => 'tmsm_aquos_spa_booking_message',
						'name'    => __('Second paragraph of the message to display', 'tmsm-aquos-spa-booking'),
					),

					array(
						'type'    => 'text',
						'id'      => 'tmsm_aquos_spa_booking_datebeforeforbidden',
						'name'    => __('Calendar Days Before Forbidden', 'tmsm-aquos-spa-booking'),
						'desc'    => '<p class="description">'
							. __(
								'Users can\'t book before this date, format YYYY-MM-DD. Leave empty for no restriction',
								'tmsm-aquos-spa-booking'
							) . '</p>',
						'default' => '',
					),

					array(
						'type'     => 'number',
						'id'       => 'tmsm_aquos_spa_booking_cartexpireminutes',
						'name'     => __('Cart Products Expires', 'tmsm-aquos-spa-booking'),
						'desc'     => '<p class="description">' . __('Minutes', 'tmsm-aquos-spa-booking') . '</p>',
						'default'  => 60,
					),

					array(
						'type'     => 'textarea',
						'id'       => 'tmsm_aquos_spa_booking_cancellationpolicy',
						'name'     => __('Cancellation Policy', 'tmsm-aquos-spa-booking'),
					),

					array(
						'type'     => 'textarea',
						'id'       => 'tmsm_aquos_spa_booking_thankyou',
						'name'     => __('Message in order received page', 'tmsm-aquos-spa-booking'),
					),

					array(
						'type'     => 'textarea',
						'id'       => 'tmsm_aquos_spa_booking_orderemail',
						'name'     => __('Message in order email', 'tmsm-aquos-spa-booking'),
					),
					array(
						'type'    => 'textarea',
						'id'      => 'tmsm_aquos_spa_booking_cancellation_text_template',
						'name'    => __('Message in appointment cancelled', 'tmsm-aquos-spa-booking'),
						'desc'    => '<p class="description">' . __('Enter the appointment cancellation text for the email using the following placeholders : [user_name], [appointment_date], [appointment_time], [service_name].', 'tmsm-aquos-spa-booking') . '</p>',
						'default' => __('Cher [user_name],\n\nVotre rendez-vous du [appointment_date] à [appointment_time] pour [service_name] a été annulé avec succès.\n\nNous sommes désolés de ne pas pouvoir vous recevoir.', 'tmsm-aquos-spa-booking'),
						
					),
			
					array(
						'type' => 'sectionend',
						'id'   => 'tmsm_aquos_spa_booking_settings',
					),

				);
			}



			return $settings;
		}

		/**
		 * Output the settings
		 *
		 * @since 1.0
		 */
		public function output()
		{

			global $current_section;

			$settings = $this->get_settings($current_section);
			WC_Admin_Settings::output_fields($settings);
		}

		/**
		 * Save settings
		 *
		 * @since 1.0
		 */
		public function save()
		{

			global $current_section;

			$settings = $this->get_settings($current_section);
			WC_Admin_Settings::save_fields($settings);
		}
	}


endif;
return new WC_Settings_Aquosspabooking();
