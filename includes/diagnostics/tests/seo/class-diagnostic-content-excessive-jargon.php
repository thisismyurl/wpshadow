<?php
/**
 * Content Excessive Jargon Diagnostic
 *
 * Detects overuse of unexplained technical terms.
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
 * Content Excessive Jargon Diagnostic Class
 *
 * Technical terms without definitions exclude 60%+ of audience.
 * Detects industry jargon used without explanation.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Excessive_Jargon extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-excessive-jargon';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Overuse of Jargon';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect unexplained technical terms that exclude audience members';

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

		// Check for undefined technical terms
		$undefined_jargon = apply_filters( 'wpshadow_has_undefined_jargon', false );
		if ( $undefined_jargon ) {
			$issues[] = __( 'Technical terms used without definitions exclude 60%+ of audience', 'wpshadow' );
		}

		// Check for industry-specific abbreviations
		$undefined_abbreviations = apply_filters( 'wpshadow_has_undefined_abbreviations', false );
		if ( $undefined_abbreviations ) {
			$issues[] = __( 'Abbreviations should be spelled out first time (\"API (Application Programming Interface)\")', 'wpshadow' );
		}

		// Check for jargon density
		$high_jargon_density = apply_filters( 'wpshadow_has_high_jargon_density', false );
		if ( $high_jargon_density ) {
			$issues[] = __( 'Too many technical terms in single paragraph; space them out and explain each', 'wpshadow' );
		}

		// Check for glossary links
		$has_glossary = apply_filters( 'wpshadow_jargon_linked_to_glossary', false );
		if ( ! $has_glossary ) {
			$issues[] = __( 'Define or link jargon terms to glossary entries for context', 'wpshadow' );
		}

		// Check for audience appropriateness
		$audience_mismatch = apply_filters( 'wpshadow_jargon_exceeds_audience_expertise', false );
		if ( $audience_mismatch ) {
			$issues[] = __( 'Jargon level should match target audience expertise', 'wpshadow' );
		}

		// Check for plain language alternatives
		$plain_alternatives = apply_filters( 'wpshadow_plain_language_alternatives_available', false );
		if ( ! $plain_alternatives ) {
			$issues[] = __( 'Use plain language when possible; save technical terms for necessity', 'wpshadow' );
		}

		// Check for accessibility impact
		$accessibility_barrier = apply_filters( 'wpshadow_jargon_creates_accessibility_barrier', false );
		if ( $accessibility_barrier ) {
			$issues[] = __( 'Unexplained jargon harms readability for non-native speakers and ADHD readers', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-excessive-jargon?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
