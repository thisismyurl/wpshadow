<?php
/**
 * Permalinks Treatment
 *
 * Handles automatic fixing of permalink structure
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\KPI_Tracker;

/**
 * Treatment for configuring friendly permalinks
 */
class Treatment_Permalinks implements Treatment_Interface {
	/**
	 * Get the finding ID this treatment addresses
	 *
	 * @return string
	 */
	public static function get_finding_id() {
		return 'permalinks-plain';
	}
	
	/**
	 * Check if this treatment can be applied
	 *
	 * @return bool True if treatment can run.
	 */
	public static function can_apply() {
		// Can always update permalink structure (doesn't require file write)
		return true;
	}
	
	/**
	 * Apply the treatment/fix
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function apply() {
		// Save current structure for undo
		$old_structure = get_option( 'permalink_structure', '' );
		update_option( 'wpshadow_prev_permalink_structure', $old_structure );
		
		// Set SEO-friendly structure
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();
		
		// Track KPI
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		
		return array(
			'success' => true,
			'message' => 'Permalink structure changed to Post name (/%postname%/). Your URLs are now SEO-friendly!',
		);
	}
	
	/**
	 * Undo the treatment (if possible)
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function undo() {
		$old_structure = get_option( 'wpshadow_prev_permalink_structure', '' );
		update_option( 'permalink_structure', $old_structure );
		flush_rewrite_rules();
		delete_option( 'wpshadow_prev_permalink_structure' );
		
		return array(
			'success' => true,
			'message' => 'Permalink structure reverted.',
		);
	}
}
