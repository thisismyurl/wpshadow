<?php
/**
 * Custom Field Visibility Diagnostic
 *
 * Checks if custom fields and post meta are properly configured for visibility.
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
 * Diagnostic_Custom_Field_Visibility Class
 *
 * Validates custom field visibility and access control.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Custom_Field_Visibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-field-visibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Field Visibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies custom fields have appropriate visibility settings';

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
		global $wpdb;

		// Count custom fields (post meta without underscore prefix)
		$custom_fields = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key NOT LIKE '\_%'"
		);

		// Count private meta (underscore prefix)
		$private_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '\_%'"
		);

		// Check if there are many visible custom fields that might need hiding
		if ( $custom_fields > 1000 && $custom_fields > $private_meta * 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of visible custom fields */
					__( 'Found %d visible custom fields. Consider prefixing meta keys with underscore (_) to hide them from the WordPress UI and improve performance.', 'wpshadow' ),
					intval( $custom_fields )
				),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-field-visibility',
			);
		}

		return null; // Custom field visibility is appropriate
	}
}
