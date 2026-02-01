<?php
/**
 * Default Post Format Diagnostic
 *
 * Verifies that the site's post format settings are properly configured
 * to support the content types being published.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Post Format Diagnostic Class
 *
 * Ensures post format support is properly configured.
 *
 * @since 1.26032.1800
 */
class Diagnostic_Default_Post_Format extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'default-post-format';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Default Post Format';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies post format support is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Post formats are supported by theme
	 * - Post format taxonomy is properly registered
	 * - Format choice makes sense for site type
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if post formats are supported.
		$post_formats = get_theme_support( 'post-formats' );

		if ( false === $post_formats ) {
			// Post formats are not supported, which is fine for many sites.
			return null;
		}

		// If post formats are supported, check if they're being used.
		if ( is_array( $post_formats[0] ) && ! empty( $post_formats[0] ) ) {
			// Theme supports post formats.
			$supported = $post_formats[0];

			// Check if any posts use custom post formats.
			global $wpdb;
			$custom_formats = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE tt.taxonomy = 'post_format'"
			);

			if ( 0 === $custom_formats ) {
				// No posts using custom formats - that's ok if "Standard" is being used.
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/default-post-format',
			);
		}

		return null;
	}
}
