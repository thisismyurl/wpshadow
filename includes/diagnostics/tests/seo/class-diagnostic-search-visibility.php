<?php
/**
 * Diagnostic: Search Engine Visibility
 *
 * Checks if search engines are allowed to index the site.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Search_Visibility Class
 *
 * Detects if search engine indexing is disabled. This is one of the most
 * critical settings for site visibility:
 *
 * - **Enabled (good):** Site appears in Google, Bing, etc. - gets organic traffic
 * - **Disabled (bad):** Site invisible to search engines - NO organic traffic
 *
 * This setting is often enabled by mistake during site development or
 * staging, then forgotten, causing massive traffic loss to production sites.
 *
 * The setting is blog_public: 1 = visible, 0 = discourage search engines
 *
 * This is CRITICAL to verify because the traffic impact is enormous.
 *
 * @since 1.2601.2200
 */
class Diagnostic_Search_Visibility extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'search-visibility';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'Search Engine Visibility';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Checks if search engines are allowed to index and rank the site in search results';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'SEO';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks the blog_public setting. If it's 0, search engines are
	 * discouraged and the site won't appear in Google results.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if search indexing disabled, null if enabled.
	 */
	public static function check() {
		$blog_public = (int) get_option( 'blog_public' );

		// Critical: Search indexing is disabled
		if ( 0 === $blog_public ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __(
					'CRITICAL: Search engine indexing is disabled! Your site will not appear in Google, Bing, or other search results. This setting is usually enabled by mistake during development. Enable search visibility immediately in WordPress Settings > Reading.',
					'wpshadow'
				),
				'severity'           => 'critical',
				'threat_level'       => 85,
				'site_health_status' => 'critical',
				'auto_fixable'       => true,
				'kb_link'            => 'https://wpshadow.com/kb/seo-search-visibility',
				'family'             => self::$family,
				'details'            => array(
					'blog_public'     => $blog_public,
					'visibility'      => 'Disabled (search engines discouraged)',
					'impact'          => 'No organic traffic from search engines',
					'recommendation'  => 'Enable search visibility in Settings > Reading > "Discourage search engines"',
				),
			);
		}

		// All good - search visibility is enabled
		return null;
	}
}
