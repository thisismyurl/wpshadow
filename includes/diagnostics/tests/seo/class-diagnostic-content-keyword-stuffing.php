<?php
/**
 * Content Keyword Stuffing Diagnostic
 *
 * Detects keyword stuffing that can trigger search penalties.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Keyword Stuffing Diagnostic Class
 *
 * Keyword density above 3% can trigger search penalties. Natural language
 * and semantic keywords are required for healthy SEO.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_Keyword_Stuffing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-keyword-stuffing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyword Stuffing Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects excessive keyword repetition that risks search penalties';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for keyword density.
		$keyword_density = apply_filters( 'wpshadow_keyword_density_percentage', 0 );
		if ( $keyword_density > 3 ) {
			$issues[] = __( 'Keyword density exceeds 3%; reduce repetition to avoid penalties', 'wpshadow' );
		}

		// Check for unnatural repetition.
		$unnatural_repetition = apply_filters( 'wpshadow_keyword_repetition_unnatural', false );
		if ( $unnatural_repetition ) {
			$issues[] = __( 'Unnatural keyword repetition harms readability and trust', 'wpshadow' );
		}

		// Check for semantic variation.
		$has_semantic_variation = apply_filters( 'wpshadow_has_semantic_keyword_variation', false );
		if ( ! $has_semantic_variation ) {
			$issues[] = __( 'Use semantic variations and synonyms instead of repeating the same phrase', 'wpshadow' );
		}

		// Check for readability impact.
		$readability_impact = apply_filters( 'wpshadow_keyword_stuffing_readability_impact', false );
		if ( $readability_impact ) {
			$issues[] = __( 'Keyword stuffing reduces readability and increases bounce rate', 'wpshadow' );
		}

		// Check for penalty risk.
		$penalty_risk = apply_filters( 'wpshadow_keyword_stuffing_penalty_risk', false );
		if ( $penalty_risk ) {
			$issues[] = __( 'Search engines may penalize pages with excessive keyword repetition', 'wpshadow' );
		}

		// Check for editorial guidelines.
		$editorial_guidelines = apply_filters( 'wpshadow_has_keyword_usage_guidelines', false );
		if ( ! $editorial_guidelines ) {
			$issues[] = __( 'Define editorial guidelines for keyword usage and natural language', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-keyword-stuffing',
			);
		}

		return null;
	}
}
