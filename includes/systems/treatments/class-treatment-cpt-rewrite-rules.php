<?php
/**
 * Treatment for CPT Rewrite Rules
 *
 * Flushes WordPress rewrite rules to regenerate permalink structure
 * for custom post types.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_CPT_Rewrite_Rules Class
 *
 * Fixes rewrite rules for custom post types.
 *
 * @since 0.6093.1200
 */
class Treatment_CPT_Rewrite_Rules extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'cpt-rewrite-rules';
	}

	/**
	 * Apply the treatment.
	 *
	 * Flushes rewrite rules to regenerate permalink structure.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 * }
	 */
	public static function apply() {
		try {
			// Flush rewrite rules.
			flush_rewrite_rules( false );

			// Store timestamp of when rules were flushed.
			update_option( 'wpshadow_rewrite_flush_time', time() );

			return array(
				'success' => true,
				'message' => __( 'Rewrite rules have been successfully flushed. Your custom post type permalinks should now work correctly.', 'wpshadow' ),
			);

		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: error message */
					__( 'Failed to flush rewrite rules: %s', 'wpshadow' ),
					$e->getMessage()
				),
			);
		}
	}
}
