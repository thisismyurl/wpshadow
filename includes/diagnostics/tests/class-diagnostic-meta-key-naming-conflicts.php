<?php
/**
 * Meta Key Naming Conflicts Diagnostic
 *
 * Checks for conflicting or ambiguous meta key naming conventions.
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
 * Diagnostic_Meta_Key_Naming_Conflicts Class
 *
 * Detects conflicting meta key names across different prefixes.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Meta_Key_Naming_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-key-naming-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Key Naming Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects conflicting meta key names that could cause issues';

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

		// Find meta keys that exist both with and without underscore prefix
		$conflicting_keys = $wpdb->get_results(
			"SELECT 
				REPLACE(meta_key, '_', '') as base_key,
				COUNT(DISTINCT meta_key) as key_variants
			FROM {$wpdb->postmeta}
			GROUP BY REPLACE(meta_key, '_', '')
			HAVING key_variants > 1
			LIMIT 10"
		);

		if ( ! empty( $conflicting_keys ) && count( $conflicting_keys ) > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of conflicting key names */
					__( 'Found %d meta keys with naming conflicts (same name with/without underscore prefix). This can cause data retrieval issues and confusion.', 'wpshadow' ),
					count( $conflicting_keys )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/meta-key-naming-conflicts',
			);
		}

		return null; // Meta key naming is consistent
	}
}
