<?php
/**
 * Post Meta Query Performance Diagnostic
 *
 * Checks if post meta queries are properly indexed and optimized.
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
 * Diagnostic_Post_Meta_Query_Performance Class
 *
 * Validates post meta query performance and indexing.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Post_Meta_Query_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-meta-query-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Meta Query Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post meta queries are properly optimized';

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

		// Check for excessive post meta records per post
		$high_meta_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			WHERE (SELECT COUNT(*) FROM {$wpdb->postmeta} pm WHERE pm.post_id = p.ID) > 100"
		);

		if ( intval( $high_meta_posts ) > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of posts with high meta counts */
					__( 'Found %d posts with more than 100 meta fields. This can significantly slow down page loads and admin operations.', 'wpshadow' ),
					intval( $high_meta_posts )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-meta-query-performance',
			);
		}

		return null; // Post meta query performance is acceptable
	}
}
