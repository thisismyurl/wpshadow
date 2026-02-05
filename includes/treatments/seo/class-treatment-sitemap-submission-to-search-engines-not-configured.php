<?php
/**
 * Sitemap Submission To Search Engines Not Configured Treatment
 *
 * Checks if sitemaps are submitted.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sitemap Submission To Search Engines Not Configured Treatment Class
 *
 * Detects missing sitemap submission.
 *
 * @since 1.6030.2352
 */
class Treatment_Sitemap_Submission_To_Search_Engines_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'sitemap-submission-to-search-engines-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Sitemap Submission To Search Engines Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if sitemaps are submitted';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if sitemap is submitted to Google Search Console
		if ( ! get_option( 'google_sitemap_submitted' ) && ! is_plugin_active( 'yoast-seo/wp-seo.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Sitemap submission to search engines is not configured. Submit your XML sitemap to Google Search Console, Bing, and other search engines for better indexing.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/sitemap-submission-to-search-engines-not-configured',
			);
		}

		return null;
	}
}
