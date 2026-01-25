<?php
/**
 * REST API Headers Treatment
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
 * Remove REST API headers from output.
 */
class Treatment_REST_API extends Treatment_Base {

	public static function get_finding_id() {
		return 'rest-api';
	}

	public static function apply() {
		update_option( 'wpshadow_rest_api_headers_disabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'security' );

		return array(
			'success' => true,
			'message' => __( 'REST API headers removed from output. The API still works but is not auto-discovered.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_rest_api_headers_disabled' );
		KPI_Tracker::log_fix_undone( self::get_finding_id() );

		return array(
			'success' => true,
			'message' => __( 'REST API headers restored in output.', 'wpshadow' ),
		);
	}
}
