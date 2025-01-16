<?php

/**
 * Fired during plugin activation
 *
 * @link       http://github.com/nicomollet/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/includes
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquos_Spa_Booking_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! wp_next_scheduled( 'tmsm_aquos_spa_booking_cronaction' ) ) {
			$midnight = strtotime( 'tomorrow midnight' );
			$midnight_plus10minutes = $midnight + 60 * 10;
			wp_schedule_event( $midnight_plus10minutes, 'daily', 'tmsm_aquos_spa_booking_cronaction' );
		}
	}

}
