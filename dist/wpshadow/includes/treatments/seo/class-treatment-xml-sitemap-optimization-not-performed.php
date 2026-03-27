<?php
/**
 * XML Sitemap Optimization Not Performed Treatment
 *
 * Checks if XML sitemaps are optimized.
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
 * XML Sitemap Optimization Not Performed Treatment Class
 *
 * Detects unoptimized XML sitemaps.
 *
 * @since 1.6093.1200
 */
class Treatment_XML_Sitemap_Optimization_Not_Performed extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-optimization-not-performed';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap Optimization Not Performed';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if XML sitemaps are optimized';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_XML_Sitemap_Optimization_Not_Performed' );
	}
}
