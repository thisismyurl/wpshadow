<?php
/**
 * Search Engine Results Page URL Structure Not Optimized Diagnostic
 *
 * Checks if SERP URL structure is optimized.
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
 * Search Engine Results Page URL Structure Not Optimized Diagnostic Class
 *
 * Detects unoptimized SERP URLs.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Search_Engine_Results_Page_URL_Structure_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-engine-results-page-url-structure-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Engine Results Page URL Structure Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SERP URL structure is optimized';

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
		// Check for clean URL structure on search pages
		if ( ! has_filter( 'search_template', 'optimize_search_url' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Search results URL structure is not optimized. Use clean URLs for search results (e.g., /search/?s=term or /search/term) for better SEO.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/search-engine-results-page-url-structure-not-optimized?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
