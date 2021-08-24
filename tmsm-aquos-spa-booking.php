<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://github.com/nicomollet/
 * @since             1.0.0
 * @package           Tmsm_Aquos_Spa_Booking
 *
 * @wordpress-plugin
 * Plugin Name:       TMSM Aquos Spa Booking
 * Plugin URI:        http://github.com/thermesmarins/tmsm-aquos-spa-booking
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.7.7
 * Author:            Nicolas Mollet
 * Author URI:        https://github.com/nicomollet
 * Requires PHP:      7.1
 * Requires at least: 5.7
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tmsm-aquos-spa-booking
 * Domain Path:       /languages
 * Github Plugin URI: http://github.com/thermesmarins/tmsm-aquos-spa-booking
 * Github Branch:     master
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TMSM_AQUOS_SPA_BOOKING_VERSION', '1.7.7' );

if(! defined('TMSM_AQUOS_SPA_BOOKING_TEMPLATES')){
	define( 'TMSM_AQUOS_SPA_BOOKING_TEMPLATES', plugin_dir_path( __FILE__ ) );
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tmsm-aquos-spa-booking-activator.php
 */
function activate_tmsm_aquos_spa_booking() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquos-spa-booking-activator.php';
	Tmsm_Aquos_Spa_Booking_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tmsm-aquos-spa-booking-deactivator.php
 */
function deactivate_tmsm_aquos_spa_booking() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquos-spa-booking-deactivator.php';
	Tmsm_Aquos_Spa_Booking_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tmsm_aquos_spa_booking' );
register_deactivation_hook( __FILE__, 'deactivate_tmsm_aquos_spa_booking' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquos-spa-booking.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tmsm_aquos_spa_booking() {

	$plugin = new Tmsm_Aquos_Spa_Booking();
	$plugin->run();

}
run_tmsm_aquos_spa_booking();
