<?php
/**
 * Feed Excerpt Configuration Diagnostic
 *
 * Checks if the feed excerpt configuration matches best practices.
 *
 * @since   1.26032.1921
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Feed_Excerpt_Configuration Class
 *
 * Checks if the feed excerpt configuration matches best practices.
 */
class Diagnostic_Feed_Excerpt_Configuration extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-excerpt-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Excerpt Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed excerpt configuration matches best practices.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1921
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$excerpt = get_option( 'rss_use_excerpt', 0 );
		if ( $excerpt ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed is set to excerpt. Consider switching to full content for better SEO and user experience.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level'=> 20,
				'auto_fixable'=> true,
				'kb_link'     => 'https://wpshadow.com/kb/feed-excerpt-configuration',
			);
		}
		return null;
	}
}
