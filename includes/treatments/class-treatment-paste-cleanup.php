<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Interface;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Paste_Cleanup implements Treatment_Interface {

	public static function get_finding_id() {
		return 'paste-cleanup';
	}

	public static function can_apply() {
		return current_user_can( 'edit_posts' );
	}

	public static function apply() {
		update_option( 'wpshadow_paste_cleanup_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => __( 'Paste cleanup enabled. Inline styles will be automatically removed from pasted content.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_paste_cleanup_enabled' );
		return array(
			'success' => true,
			'message' => __( 'Paste cleanup disabled.', 'wpshadow' ),
		);
	}
}
