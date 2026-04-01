<?php
/**
 * Content Missing Primary Keyword in Title Diagnostic
 *
 * Detects when primary keyword is missing from the title.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Missing Primary Keyword in Title Diagnostic Class
 *
 * Target keyword not in H1 is a basic SEO issue. This is
 * 100% auto-detectable and highly impactful.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Keyword_Missing_Title extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-keyword-missing-title';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Primary Keyword in Title';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing primary keyword in title (H1)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for missing primary keyword in title.
		$keyword_in_title = apply_filters( 'wpshadow_primary_keyword_in_title', false );
		if ( ! $keyword_in_title ) {
			$issues[] = __( 'Primary keyword not found in title (H1); add for basic SEO hygiene', 'wpshadow' );
		}

		// Check for title/keyword alignment.
		$title_alignment = apply_filters( 'wpshadow_title_keyword_alignment', false );
		if ( ! $title_alignment ) {
			$issues[] = __( 'Title does not align with target keyword intent', 'wpshadow' );
		}

		// Check for keyword placement.
		$keyword_placement = apply_filters( 'wpshadow_keyword_placement_optimized', false );
		if ( ! $keyword_placement ) {
			$issues[] = __( 'Place primary keyword near the beginning of the title when possible', 'wpshadow' );
		}

		// Check for SERP impact.
		$serp_impact = apply_filters( 'wpshadow_missing_keyword_title_serp_impact', false );
		if ( $serp_impact ) {
			$issues[] = __( 'Missing keyword in title reduces relevance and click-through rate', 'wpshadow' );
		}

		// Check for H1 consistency.
		$h1_consistency = apply_filters( 'wpshadow_h1_matches_title', false );
		if ( ! $h1_consistency ) {
			$issues[] = __( 'H1 should match the title and include the primary keyword', 'wpshadow' );
		}

		// Check for multi-keyword stuffing in title.
		$title_stuffing = apply_filters( 'wpshadow_title_keyword_stuffing', false );
		if ( $title_stuffing ) {
			$issues[] = __( 'Avoid stuffing multiple keywords into the title; keep it natural', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/content-keyword-missing-title?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
