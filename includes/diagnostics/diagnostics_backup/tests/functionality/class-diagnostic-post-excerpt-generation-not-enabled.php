<?php
/**
 * Post Excerpt Generation Not Enabled Diagnostic
 *
 * Checks if post excerpts are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Excerpt Generation Not Enabled Diagnostic Class
 *
 * Detects missing post excerpts.
 *
 * @since 1.2601.2315
 */
class Diagnostic_Post_Excerpt_Generation_Not_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-excerpt-generation-not-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Excerpt Generation Not Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post excerpts are available';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if any posts have empty post_excerpt field
		$posts_without_excerpt = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_excerpt = '' AND post_type = 'post' LIMIT 100"
		);

		if ( $posts_without_excerpt > 50 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d posts are missing excerpts. Excerpts improve SEO and provide better content previews.', 'wpshadow' ),
					absint( $posts_without_excerpt )
				),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/post-excerpt-generation-not-enabled',
			);
		}

		return null;
	}
}
