<?php
/**
 * Sitemap XML Not Submitted To Search Engines Treatment
 *
 * Checks if sitemap is submitted.
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
 * Sitemap XML Not Submitted To Search Engines Treatment Class
 *
 * Detects unsubmitted sitemap.
 *
 * @since 1.6030.2352
 */
class Treatment_Sitemap_XML_Not_Submitted_To_Search_Engines extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'sitemap-xml-not-submitted-to-search-engines';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Sitemap XML Not Submitted To Search Engines';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if sitemap is submitted';

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
		// Check if sitemap plugin or native support exists
		if ( ! is_plugin_active( 'yoast-seo/wp-seo.php' ) && ! is_plugin_active( 'google-sitemap-generator/sitemap.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'XML sitemap is not submitted to search engines. Generate and submit your sitemap to Google Search Console for better indexing.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/sitemap-xml-not-submitted-to-search-engines',
			);
		}

		return null;
	}
}
