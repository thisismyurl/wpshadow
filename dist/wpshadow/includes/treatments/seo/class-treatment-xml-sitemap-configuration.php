<?php
/**
 * XML Sitemap Configuration Treatment
 *
 * Tests if site properly generates and submits XML sitemaps to search engines.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML Sitemap Configuration Treatment Class
 *
 * Validates that the site generates XML sitemaps and submits them
 * to search engines for better indexing and discovery.
 *
 * @since 1.6093.1200
 */
class Treatment_XML_Sitemap_Configuration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-configuration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap Configuration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site properly generates and submits XML sitemaps to search engines';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * Tests XML sitemap configuration including generation, index file,
	 * and search engine submission.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_XML_Sitemap_Configuration' );
	}
}
