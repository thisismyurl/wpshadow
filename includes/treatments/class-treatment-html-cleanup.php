<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Interface;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_HTML_Cleanup implements Treatment_Interface {

	public static function get_finding_id() {
		return 'html-cleanup';
	}

	public static function can_apply() {
		return current_user_can( 'manage_options' );
	}

	public static function apply() {
		update_option( 'wpshadow_html_cleanup_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => __( 'HTML minification enabled. Comments and whitespace will be removed from HTML output.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_html_cleanup_enabled' );
		return array(
			'success' => true,
			'message' => __( 'HTML minification disabled.', 'wpshadow' ),
		);
	}
}
