<?php
/**
 * jQuery Cleanup Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to dequeue jQuery on front-end pages.
 */
class Treatment_jQuery_Cleanup implements Treatment_Interface {
	public static function get_finding_id() {
		return 'jquery-front-loading';
	}
	
	public static function can_apply() {
		return true;
	}
	
	public static function apply() {
		update_option( 'wpshadow_jquery_cleanup_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => 'Front-end jQuery will be dequeued when not required.',
		);
	}
	
	public static function undo() {
		delete_option( 'wpshadow_jquery_cleanup_enabled' );
		return array(
			'success' => true,
			'message' => 'jQuery cleanup disabled.',
		);
	}
}
