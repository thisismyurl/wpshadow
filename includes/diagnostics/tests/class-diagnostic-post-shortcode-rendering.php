<?php
/**
 * Post Shortcode Rendering Diagnostic
 *
 * Checks if shortcodes are properly rendered in post content.
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
 * Diagnostic_Post_Shortcode_Rendering Class
 *
 * Validates post shortcode rendering and functionality.
 *
 * @since 1.26033.0901
 */
class Diagnostic_Post_Shortcode_Rendering extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-shortcode-rendering';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Shortcode Rendering';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if shortcodes are properly rendered in posts';

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

		// Count posts with shortcodes
		$posts_with_shortcodes = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_content REGEXP '\\[+[a-zA-Z0-9_-]+.*\\]'
			AND post_type IN ('post', 'page')"
		);

		if ( intval( $posts_with_shortcodes ) > 0 ) {
			global $shortcode_tags;
			$registered_count = count( $shortcode_tags );

			if ( $registered_count < 5 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: number of posts with shortcodes, %d: number of registered shortcodes */
						__( 'Found %d posts using shortcodes, but only %d shortcode handlers are registered. Missing shortcode handlers will display raw shortcode text.', 'wpshadow' ),
						intval( $posts_with_shortcodes ),
						$registered_count
					),
					'severity'     => 'medium',
					'threat_level' => 45,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/post-shortcode-rendering',
				);
			}
		}

		return null; // Post shortcode rendering is healthy
	}
}
