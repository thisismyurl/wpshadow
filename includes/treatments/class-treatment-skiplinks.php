<?php
/**
 * Skiplinks Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to enable skiplinks output.
 */
class Treatment_Skiplinks extends Treatment_Base {
	public static function get_finding_id() {
		return 'skiplinks-missing';
	}

	public static function apply() {
		update_option( 'wpshadow_skiplinks_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => 'Skiplinks enabled for improved accessibility.',
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_skiplinks_enabled' );
		return array(
			'success' => true,
			'message' => 'Skiplinks disabled.',
		);
	}
}
