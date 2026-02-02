<?php
/**
 * Post Excerpt Generation Diagnostic
 *
 * Checks if post excerpts are properly generated and not truncated incorrectly.
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
 * Diagnostic_Post_Excerpt_Generation Class
 *
 * Validates post excerpt generation and configuration.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Post_Excerpt_Generation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-excerpt-generation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Excerpt Generation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post excerpts are properly generated';

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

		// Check for posts with very long content but no excerpt
		$posts_no_excerpt = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_excerpt = ''
			AND LENGTH(post_content) > 1000
			AND post_type = 'post'
			AND post_status = 'publish'"
		);

		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'post'
			AND post_status = 'publish'"
		);

		if ( $total_posts > 0 && intval( $posts_no_excerpt ) > ( intval( $total_posts ) * 0.7 ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: percentage of posts without excerpts */
					__( '%d%% of published posts lack manual excerpts. Auto-generated excerpts may be cut off mid-sentence or lose important context.', 'wpshadow' ),
					intval( ( intval( $posts_no_excerpt ) / intval( $total_posts ) ) * 100 )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-excerpt-generation',
			);
		}

		return null; // Post excerpt generation is adequate
	}
}
