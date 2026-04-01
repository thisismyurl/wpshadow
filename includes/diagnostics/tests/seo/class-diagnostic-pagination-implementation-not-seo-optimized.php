<?php
/**
 * Pagination Implementation Not SEO Optimized Diagnostic
 *
 * Checks if pagination is SEO optimized.
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
 * Pagination Implementation Not SEO Optimized Diagnostic Class
 *
 * Detects unoptimized pagination.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Pagination_Implementation_Not_SEO_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pagination-implementation-not-seo-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pagination Implementation Not SEO Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pagination is SEO optimized';

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
		// Check for pagination rel attributes
		if ( ! has_filter( 'wp_link_pages_args', 'wp_filter_pagination' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Pagination is not SEO optimized. Add rel="next" and rel="prev" tags to pagination links for proper content relationship signaling.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/pagination-implementation-not-seo-optimized?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
