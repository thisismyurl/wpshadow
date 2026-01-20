<?php
/**
 * Head Cleanup Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to remove common head cruft (emoji, oEmbed discovery, RSD, shortlink).
 */
class Treatment_Head_Cleanup implements Treatment_Interface {
	public static function get_finding_id() {
		return 'head-cleanup-needed';
	}
	
	public static function can_apply() {
		return true;
	}
	
	public static function apply() {
		update_option( 'wpshadow_head_cleanup_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => 'Head cleanup enabled. Emoji, oEmbed discovery, RSD, and shortlink tags will be removed.',
		);
	}
	
	public static function undo() {
		delete_option( 'wpshadow_head_cleanup_enabled' );
		return array(
			'success' => true,
			'message' => 'Head cleanup disabled. Default head tags restored.',
		);
	}
}
