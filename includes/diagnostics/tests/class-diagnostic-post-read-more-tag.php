<?php
/**
 * Post Read More Tag Diagnostic
 *
 * Checks if the Read More tag is properly configured in posts.
 *
 * @since   1.26033.0901
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Post_Read_More_Tag Class
 *
 * Validates Read More tag configuration.
 *
 * @since 1.26033.0901
 */
class Diagnostic_Post_Read_More_Tag extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-read-more-tag';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Read More Tag';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Read More tags are properly used';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0901
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count posts with Read More tags
		$posts_with_readmore = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_content LIKE '%<!--more%'
			AND post_type IN ('post', 'page')"
		);

		// Count total published posts
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')"
		);

		if ( $total_posts > 0 && intval( $posts_with_readmore ) < ( intval( $total_posts ) * 0.1 ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Few posts use the Read More tag. Consider adding Read More tags to improve blog archive page usability and load times.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-read-more-tag',
			);
		}

		return null; // Read More tag usage is adequate
	}
}
