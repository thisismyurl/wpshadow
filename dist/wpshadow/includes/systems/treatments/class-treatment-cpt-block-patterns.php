<?php
/**
 * Treatment for CPT Block Patterns
 *
 * Re-initializes block patterns registration for custom post types.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Content\CPT_Block_Patterns;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_CPT_Block_Patterns Class
 *
 * Fixes block patterns registration.
 *
 * @since 0.6093.1200
 */
class Treatment_CPT_Block_Patterns extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'cpt-block-patterns';
	}

	/**
	 * Apply the treatment.
	 *
	 * Re-registers block patterns by calling the initialization method.
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
			// Check if class exists.
			if ( ! class_exists( 'WPShadow\Content\CPT_Block_Patterns' ) ) {
				return array(
					'success' => false,
					'message' => __( 'Block Patterns class not found. Please ensure the CPT Block Patterns feature file exists.', 'wpshadow' ),
				);
			}

			// Clear any cached patterns registry.
			if ( function_exists( 'wp_cache_delete' ) ) {
				wp_cache_delete( 'block_patterns', 'wpshadow' );
			}

			// Re-run initialization (patterns are registered on init hook).
			// We'll trigger a transient that tells the system to re-register on next page load.
			set_transient( 'wpshadow_reinit_block_patterns', true, MINUTE_IN_SECONDS );

			return array(
				'success' => true,
				'message' => __( 'Block patterns will be re-registered on the next page load. Please refresh your browser to see the changes.', 'wpshadow' ),
			);

		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: error message */
					__( 'Failed to fix block patterns: %s', 'wpshadow' ),
					$e->getMessage()
				),
			);
		}
	}
}
