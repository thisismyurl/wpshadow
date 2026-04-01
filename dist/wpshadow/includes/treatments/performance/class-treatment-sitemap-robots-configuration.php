<?php
/**
 * Sitemap and Robots.txt Configuration Treatment
 *
 * Checks if XML sitemaps and robots.txt are properly configured for
 * search engine crawling optimization.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sitemap and Robots.txt Configuration Treatment Class
 *
 * Verifies SEO configuration:
 * - XML sitemap presence
 * - robots.txt file
 * - Crawlability settings
 * - Search engine directives
 *
 * @since 0.6093.1200
 */
class Treatment_Sitemap_Robots_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'sitemap-robots-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Sitemap and Robots.txt Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for XML sitemap and robots.txt optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Sitemap_Robots_Configuration' );
	}
}
