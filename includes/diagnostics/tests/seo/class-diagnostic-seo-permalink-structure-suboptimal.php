<?php
/**
 * SEO Permalink Structure Suboptimal Diagnostic
 *
 * Checks if permalink structure is SEO-optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Permalink Structure Suboptimal Diagnostic Class
 *
 * Detects non-SEO-optimized permalink structures.
 *
 * @since 1.2601.2310
 */
class Diagnostic_SEO_Permalink_Structure_Suboptimal extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'seo-permalink-structure-suboptimal';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SEO Permalink Structure Suboptimal';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if permalink structure is SEO-friendly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$permalink_structure = get_option( 'permalink_structure', '' );

		// Check for bad structures (plain numbers, etc)
		if ( empty( $permalink_structure ) || $permalink_structure === '/?p=%post_id%' ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Permalink structure is not SEO-friendly. Using plain or numeric URLs reduces keyword relevance in search results.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/seo-permalink-structure-suboptimal',
			);
		}

		// Check for overly complex structures
		if ( substr_count( $permalink_structure, '/' ) > 4 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Permalink structure is overly complex. Simpler URLs are better for SEO and user experience.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/seo-permalink-structure-suboptimal',
			);
		}

		return null;
	}
}
