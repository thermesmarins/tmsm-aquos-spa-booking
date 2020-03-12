<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

require( 'vendors/wp-async-request.php' );
require( 'vendors/wp-background-process.php' );

/**
 * Extends the background process class
 *
 * @since 2.7
 *
 * @see WP_Background_Process
 */
class Tmsm_Aquos_Spa_Booking_Background_Process extends WP_Background_Process {

	/**
	 * Specific action identifier
	 *
	 * @access protected
	 * @var string Action identifier
	 */
	protected $action = 'tmsm_asb_bp';

	/**
	 * Task
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return null
	 */
	protected function task( $item ) {

		error_log('Tmsm_Aquos_Spa_Booking_Background_Process task:');
		error_log(print_r($item, true));
		sleep(10);

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		error_log('Tmsm_Aquos_Spa_Booking_Background_Process complete');
		parent::complete();
	}

}
$GLOBALS['tmsm_asb_bp'] = new Tmsm_Aquos_Spa_Booking_Background_Process();