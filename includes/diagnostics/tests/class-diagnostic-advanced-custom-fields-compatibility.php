<?php
/**
 * Advanced Custom Fields Compatibility Diagnostic
 *
 * Checks for compatibility issues with Advanced Custom Fields.
 *
 * @since   1.26033.0800
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Advanced_Custom_Fields_Compatibility Class
 *
 * Validates ACF compatibility and configuration.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Advanced_Custom_Fields_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'advanced-custom-fields-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Advanced Custom Fields Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for ACF compatibility issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'meta';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if ACF is active
		$acf_active = function_exists( 'get_field' );

		if ( ! $acf_active ) {
			return null; // ACF not active, no compatibility issues
		}

		// Check for orphaned ACF field groups
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$field_groups = acf_get_field_groups();
			$orphaned_count = 0;

			foreach ( $field_groups as $group ) {
				if ( empty( $group['location'] ) ) {
					$orphaned_count++;
				}
			}

			if ( $orphaned_count > 0 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: number of orphaned field groups */
						__( 'Found %d ACF field groups with no location assignment. These won\'t display properly and should be deleted or configured.', 'wpshadow' ),
						$orphaned_count
					),
					'severity'     => 'low',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/advanced-custom-fields-compatibility',
				);
			}
		}

		return null; // ACF compatibility is healthy
	}
}
