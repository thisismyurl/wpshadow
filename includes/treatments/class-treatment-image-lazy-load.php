<?php
/**
 * Image Lazy Load Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to enforce lazy loading for images.
 */
class Treatment_Image_Lazy_Load implements Treatment_Interface {
	public static function get_finding_id() {
		return 'image-lazyload-disabled';
	}
	
	public static function can_apply() {
		return true;
	}
	
	public static function apply() {
		update_option( 'wpshadow_force_lazyload', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => 'Image lazy loading enforced for front-end images.',
		);
	}
	
	public static function undo() {
		delete_option( 'wpshadow_force_lazyload' );
		return array(
			'success' => true,
			'message' => 'Image lazy loading enforcement removed.',
		);
	}
}
