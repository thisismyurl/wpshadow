<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Interface;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Pre_Publish_Review implements Treatment_Interface {

	public static function get_finding_id() {
		return 'pre-publish-review';
	}

	public static function can_apply() {
		return current_user_can( 'publish_posts' );
	}

	public static function apply() {
		update_option( 'wpshadow_pre_publish_review_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => __( 'Pre-publish review enabled. Posts will be checked before publishing.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_pre_publish_review_enabled' );
		return array(
			'success' => true,
			'message' => __( 'Pre-publish review disabled.', 'wpshadow' ),
		);
	}
}
