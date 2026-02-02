<?php
/**
 * Feed Summary vs Full Diagnostic
 *
 * Checks if the feed is set to summary or full content and recommends best practice.
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
 * Diagnostic_Feed_Summary_vs_Full Class
 *
 * Checks if the feed is set to summary or full content and recommends best practice.
 */
class Diagnostic_Feed_Summary_vs_Full extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-summary-vs-full';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Summary vs Full';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed is set to summary or full content and recommends best practice.';

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
		$show_full = get_option( 'rss_use_excerpt', 0 ) ? false : true;
		if ( ! $show_full ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed is set to summary. Consider switching to full content for better user experience.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level'=> 20,
				'auto_fixable'=> true,
				'kb_link'     => 'https://wpshadow.com/kb/feed-summary-vs-full',
			);
		}
		return null;
	}
}
