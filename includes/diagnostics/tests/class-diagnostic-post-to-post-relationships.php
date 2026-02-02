<?php
/**
 * Post-to-Post Relationships Diagnostic
 *
 * Checks for broken or missing post-to-post relationships.
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
 * Diagnostic_Post_To_Post_Relationships Class
 *
 * Validates post-to-post relationship integrity.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Post_To_Post_Relationships extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-to-post-relationships';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post-to-Post Relationships';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for broken post-to-post relationship references';

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

		// Check for orphaned post relationships in meta
		$orphaned_relations = $wpdb->get_var(
			"SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->postmeta} pm
			WHERE pm.meta_value REGEXP '^[0-9]+$'
			AND pm.meta_key LIKE '%post%' OR pm.meta_key LIKE '%related%'
			AND NOT EXISTS (SELECT 1 FROM {$wpdb->posts} p WHERE p.ID = CAST(pm.meta_value AS UNSIGNED))"
		);

		if ( intval( $orphaned_relations ) > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of posts with broken relationships */
					__( 'Found %d posts with broken post-to-post relationships. These relationships reference posts that no longer exist.', 'wpshadow' ),
					intval( $orphaned_relations )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-to-post-relationships',
			);
		}

		return null; // Post relationships are intact
	}
}
