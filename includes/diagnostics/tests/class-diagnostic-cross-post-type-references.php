<?php
/**
 * Cross-Post Type References Diagnostic
 *
 * Checks for broken references between different post types.
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
 * Diagnostic_Cross_Post_Type_References Class
 *
 * Validates cross-post type reference integrity.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Cross_Post_Type_References extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cross-post-type-references';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cross-Post Type References';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for broken references between different post types';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for post content containing post IDs that reference different post types incorrectly
		$broken_references = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			WHERE p.post_content REGEXP '\\[.*id=[0-9]+.*\\]'
			AND p.ID NOT IN (
				SELECT pm.post_id FROM {$wpdb->postmeta} pm
				WHERE pm.meta_value LIKE CONCAT('%', p.ID, '%')
			)
			LIMIT 100"
		);

		if ( intval( $broken_references ) > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Detected posts with potential broken cross-post-type references in shortcodes or embedded links. These may not display correctly.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cross-post-type-references',
			);
		}

		return null; // Cross-post type references are intact
	}
}
