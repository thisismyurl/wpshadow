<?php
/**
 * Content Syndication Feed Optimization Not Implemented Diagnostic
 *
 * Checks if feed optimization is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Syndication Feed Optimization Not Implemented Diagnostic Class
 *
 * Detects missing feed optimization.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_Syndication_Feed_Optimization_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-syndication-feed-optimization-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Syndication Feed Optimization Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feed optimization is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for feed optimization
		if ( ! has_filter( 'the_content_feed', 'wp_feed_filter' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content syndication feed is not optimized. Optimize RSS feeds to improve content distribution and engagement.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-syndication-feed-optimization-not-implemented',
			);
		}

		return null;
	}
}
