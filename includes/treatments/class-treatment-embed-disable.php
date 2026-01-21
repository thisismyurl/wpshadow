<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Embed_Disable extends Treatment_Base {

	public static function get_finding_id() {
		return 'embed-disable';
	}

	public static function apply() {
		update_option( 'wpshadow_embed_disable_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => __( 'WordPress embed scripts disabled. This improves performance when embeds are not used.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_embed_disable_enabled' );
		return array(
			'success' => true,
			'message' => __( 'WordPress embed scripts re-enabled.', 'wpshadow' ),
		);
	}
}
