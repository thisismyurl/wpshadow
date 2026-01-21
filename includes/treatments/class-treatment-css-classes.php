<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_CSS_Classes extends Treatment_Base {

	public static function get_finding_id(): string {
		return 'css-classes';
	}

	public static function apply(): array {
		update_option( 'wpshadow_css_class_cleanup_enabled', true, false );

		KPI_Tracker::log_fix_applied( 'css-classes', 'performance' );

		return array(
			'success' => true,
			'message' => __( 'CSS class cleanup enabled. Body, post, and navigation classes will be simplified.', 'wpshadow' ),
		);
	}

	public static function undo(): array {
		delete_option( 'wpshadow_css_class_cleanup_enabled' );

		return array(
			'success' => true,
			'message' => __( 'CSS class cleanup disabled. Original classes restored.', 'wpshadow' ),
		);
	}
}
