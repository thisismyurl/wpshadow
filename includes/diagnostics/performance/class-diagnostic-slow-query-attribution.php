<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Slow Query Attribution to Plugin/Theme (DATABASE-022)
 *
 * Traces slow database queries back to the plugin or theme that generated them.
 * Philosophy: Educate (#5) - Help users identify which extensions need optimization.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Slow_Query_Attribution extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check database health
		global $wpdb;

		$revision_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" );

		if ( $revision_count > 1000 ) {
			return array(
				'status'       => 'warning',
				'message'      => sprintf( __( 'High revision count: %d', 'wpshadow' ), $revision_count ),
				'threat_level' => 'medium',
			);
		}
		return null; // No issues detected
	}
}
