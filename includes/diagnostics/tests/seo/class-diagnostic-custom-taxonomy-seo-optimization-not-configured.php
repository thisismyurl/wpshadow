<?php
/**
 * Custom Taxonomy SEO Optimization Not Configured Diagnostic
 *
 * Checks if custom taxonomy SEO is optimized.
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
 * Custom Taxonomy SEO Optimization Not Configured Diagnostic Class
 *
 * Detects unoptimized custom taxonomy SEO.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Custom_Taxonomy_SEO_Optimization_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-taxonomy-seo-optimization-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Taxonomy SEO Optimization Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom taxonomy SEO is optimized';

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
		// Check for custom taxonomies
		$taxonomies = get_taxonomies( array( '_builtin' => false ), 'objects' );

		if ( empty( $taxonomies ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Custom taxonomy SEO is not optimized. Optimize custom taxonomy pages with proper titles, descriptions, and canonical tags.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/custom-taxonomy-seo-optimization-not-configured',
			);
		}

		return null;
	}
}
