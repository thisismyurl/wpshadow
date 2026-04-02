<?php
/**
 * RSS Feed Summary Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the seo gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Rss_Feed_Summary_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Rss_Feed_Summary extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rss-feed-summary';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'RSS Feed Summary';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the RSS feed is configured to output excerpts rather than full post content, reducing content scraping and preserving reader engagement on the site.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check rss_use_excerpt against content syndication strategy.
	 *
	 * TODO Fix Plan:
	 * - Choose full text or summary feeds intentionally.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$rss_use_excerpt = (int) get_option( 'rss_use_excerpt', 0 );

		// 0 = full text, 1 = summary/excerpt.
		if ( 0 === $rss_use_excerpt ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'RSS feeds are set to output the full text of each post. This allows content scrapers to republish your content verbatim, creating duplicate-content issues that can dilute your SEO rankings. Consider switching to summaries under Settings → Reading → "For each article in a feed, include" → Summary.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/rss-feed-summary',
				'details'      => array(
					'rss_use_excerpt' => 0,
				),
			);
		}

		return null;
	}
}
