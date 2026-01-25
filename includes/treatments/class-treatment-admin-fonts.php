<?php
/**
 * WP Admin Fonts Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove Google Fonts from WordPress admin.
 */
class Treatment_Admin_Fonts extends Treatment_Base {

	public static function get_finding_id() {
		return 'admin-fonts';
	}

	public static function apply() {
		update_option( 'wpshadow_admin_fonts_disabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'performance' );

		return array(
			'success' => true,
			'message' => __( 'Google Fonts disabled in WP Admin. The admin will now use system fonts.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_admin_fonts_disabled' );
		KPI_Tracker::log_fix_undone( self::get_finding_id() );

		return array(
			'success' => true,
			'message' => __( 'Google Fonts re-enabled in WP Admin.', 'wpshadow' ),
		);
	}
}
