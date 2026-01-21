<?php
/**
 * Iframe Busting Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to enable clickjacking protection headers.
 */
class Treatment_Iframe_Busting extends Treatment_Base {
	public static function get_finding_id() {
		return 'iframe-busting-missing';
	}
	
	public static function apply() {
		update_option( 'wpshadow_iframe_busting_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => 'Clickjacking protection enabled (SAMEORIGIN frame headers).',
		);
	}
	
	public static function undo() {
		delete_option( 'wpshadow_iframe_busting_enabled' );
		return array(
			'success' => true,
			'message' => 'Clickjacking protection disabled.',
		);
	}
}
