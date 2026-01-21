<?php
/**
 * Resource Hints Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to enable preconnect/preload hints for known hosts.
 */
class Treatment_Resource_Hints extends Treatment_Base {
	public static function get_finding_id() {
		return 'resource-hints-missing';
	}
	
	public static function apply() {
		update_option( 'wpshadow_resource_hints_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => 'Resource hints enabled (preconnect/preload) for primary hosts.',
		);
	}
	
	public static function undo() {
		delete_option( 'wpshadow_resource_hints_enabled' );
		return array(
			'success' => true,
			'message' => 'Resource hints disabled.',
		);
	}
}
