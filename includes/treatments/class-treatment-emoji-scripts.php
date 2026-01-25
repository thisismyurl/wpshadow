<?php
/**
 * Emoji Scripts Treatment
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
 * Remove emoji detection scripts.
 */
class Treatment_Emoji_Scripts extends Treatment_Base {

	public static function get_finding_id() {
		return 'emoji-scripts';
	}

	public static function apply() {
		update_option( 'wpshadow_emoji_scripts_disabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'performance' );

		return array(
			'success' => true,
			'message' => __( 'Emoji scripts disabled. Emojis will still display using native browser support.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_emoji_scripts_disabled' );
		KPI_Tracker::log_fix_undone( self::get_finding_id() );

		return array(
			'success' => true,
			'message' => __( 'Emoji scripts re-enabled.', 'wpshadow' ),
		);
	}
}
