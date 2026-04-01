<?php
/**
 * Permalink Structure SEO Diagnostic
 *
 * Verifies permalink structure is SEO-friendly and follows best practices.
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
 * Permalink Structure SEO Diagnostic Class
 *
 * Analyzes permalink configuration for SEO optimization.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Permalink_Structure_SEO extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-structure-seo';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Structure SEO';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies permalink structure is SEO-friendly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues              = array();
		$permalink_structure = get_option( 'permalink_structure' );

		// Plain permalinks are not SEO-friendly.
		if ( empty( $permalink_structure ) ) {
			$issues[] = __( 'Using default plain permalinks which are poor for SEO', 'wpshadow' );
			$severity = 'high';
		} else {
			// Check for numeric-only permalinks.
			if ( '/%post_id%/' === $permalink_structure ) {
				$issues[] = __( 'Numeric permalinks provide no SEO benefit', 'wpshadow' );
				$severity = 'medium';
			}

			// Check for date-based permalinks (less relevant in modern SEO).
			if ( false !== strpos( $permalink_structure, '%year%' ) ||
			     false !== strpos( $permalink_structure, '%monthnum%' ) ||
			     false !== strpos( $permalink_structure, '%day%' ) ) {
				$issues[] = __( 'Date-based permalinks can make content appear outdated', 'wpshadow' );
				$severity = 'low';
			}

			// Check if postname is included (best for SEO).
			if ( false === strpos( $permalink_structure, '%postname%' ) ) {
				$issues[] = __( 'Permalink structure does not include post name (recommended for SEO)', 'wpshadow' );
				$severity = 'medium';
			}

			// Check for category in permalink.
			if ( false !== strpos( $permalink_structure, '%category%' ) ) {
				$issues[] = __( 'Category in permalinks can cause issues when changing categories', 'wpshadow' );
				$severity = 'low';
			}
		}

		// Check category base.
		$category_base = get_option( 'category_base' );
		if ( empty( $category_base ) ) {
			// Default 'category' slug is verbose.
			$issues[] = __( 'Using default "category" base in URLs (consider shortening)', 'wpshadow' );
			$severity = isset( $severity ) ? $severity : 'low';
		}

		// Check tag base.
		$tag_base = get_option( 'tag_base' );
		if ( empty( $tag_base ) ) {
			// Default 'tag' slug.
			$issues[] = __( 'Using default "tag" base in URLs', 'wpshadow' );
			$severity = isset( $severity ) ? $severity : 'low';
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => $severity ?? 'low',
				'threat_level' => ( $severity ?? 'low' ) === 'high' ? 60 : ( $severity === 'medium' ? 40 : 20 ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/permalink-structure-seo?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
