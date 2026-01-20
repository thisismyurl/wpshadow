<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Interface;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Interactivity_Cleanup implements Treatment_Interface {

	public static function get_finding_id() {
		return 'interactivity-cleanup';
	}

	public static function can_apply() {
		return current_user_can( 'manage_options' );
	}

	public static function apply() {
		update_option( 'wpshadow_interactivity_cleanup_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => __( 'Modern block features disabled. This improves performance when interactive blocks are not used.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_interactivity_cleanup_enabled' );
		return array(
			'success' => true,
			'message' => __( 'Modern block features re-enabled.', 'wpshadow' ),
		);
	}
}
