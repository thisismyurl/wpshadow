<?php
/**
 * XML Sitemap Optimization Not Performed Diagnostic
 *
 * Checks if XML sitemaps are optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML Sitemap Optimization Not Performed Diagnostic Class
 *
 * Detects unoptimized XML sitemaps.
 *
 * @since 1.2601.2352
 */
class Diagnostic_XML_Sitemap_Optimization_Not_Performed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-optimization-not-performed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap Optimization Not Performed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if XML sitemaps are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if sitemap optimization is implemented
		global $wp_rewrite;
		if ( ! $wp_rewrite || ! $wp_rewrite->using_permalinks() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'XML sitemap optimization is not performed. Limit sitemaps to 50,000 URLs per file, use compression, and set proper lastmod dates for faster indexing.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/xml-sitemap-optimization-not-performed',
			);
		}

		return null;
	}
}
