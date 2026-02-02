<?php
/**
 * Post Parent-Child Relationships Diagnostic
 *
 * Checks for orphaned posts and broken hierarchy relationships.
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
 * Diagnostic_Post_Parent_Child_Relationships Class
 *
 * Validates post hierarchy and parent-child relationships.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Post_Parent_Child_Relationships extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-parent-child-relationships';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Parent-Child Relationships';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for orphaned posts and broken hierarchy relationships';

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

		// Find posts with non-existent parent posts
		$orphaned_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p1
			WHERE p1.post_parent > 0
			AND NOT EXISTS (SELECT 1 FROM {$wpdb->posts} p2 WHERE p2.ID = p1.post_parent)
			AND p1.post_type IN ('page', 'post')"
		);

		if ( intval( $orphaned_count ) > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of orphaned posts */
					__( 'Found %d posts with non-existent parent posts. This can break page hierarchy and menus. Consider reassigning or deleting these orphaned posts.', 'wpshadow' ),
					intval( $orphaned_count )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-parent-child-relationships',
			);
		}

		return null; // Post relationships are healthy
	}
}
