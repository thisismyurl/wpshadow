<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Content_Optimizer extends Treatment_Base {

	public static function get_finding_id() {
		return 'content-optimizer';
	}

	public static function apply() {
		update_option( 'wpshadow_content_optimizer_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => __( 'Content quality optimizer enabled. You will see quality checks when editing posts.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_content_optimizer_enabled' );
		return array(
			'success' => true,
			'message' => __( 'Content optimizer disabled.', 'wpshadow' ),
		);
	}
}
