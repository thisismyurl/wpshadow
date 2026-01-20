<?php
/**
 * WP Generator Tag Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Interface;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove WordPress generator meta tag.
 */
class Treatment_WP_Generator implements Treatment_Interface {

	public static function get_finding_id() {
		return 'wp-generator';
	}

	public static function can_apply() {
		return current_user_can( 'manage_options' );
	}

	public static function apply() {
		update_option( 'wpshadow_wp_generator_disabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'security' );
		
		return array(
			'success' => true,
			'message' => __( 'WordPress generator tag removed from page head. Your WordPress version is now hidden.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_wp_generator_disabled' );
		KPI_Tracker::log_fix_undone( self::get_finding_id() );
		
		return array(
			'success' => true,
			'message' => __( 'WordPress generator tag restored in page head.', 'wpshadow' ),
		);
	}
}
