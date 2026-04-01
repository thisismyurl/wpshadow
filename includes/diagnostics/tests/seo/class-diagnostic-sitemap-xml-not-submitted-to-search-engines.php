<?php
/**
 * Sitemap XML Not Submitted To Search Engines Diagnostic
 *
 * Checks if sitemap is submitted.
 *
 * @package    WPShadow
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
 * Sitemap XML Not Submitted To Search Engines Diagnostic Class
 *
 * Detects unsubmitted sitemap.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Sitemap_XML_Not_Submitted_To_Search_Engines extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sitemap-xml-not-submitted-to-search-engines';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sitemap XML Not Submitted To Search Engines';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if sitemap is submitted';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if sitemap plugin or native support exists
		if ( ! is_plugin_active( 'yoast-seo/wp-seo.php' ) && ! is_plugin_active( 'google-sitemap-generator/sitemap.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'XML sitemap is not submitted to search engines. Generate and submit your sitemap to Google Search Console for better indexing.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/sitemap-xml-not-submitted-to-search-engines?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
