<?php
/**
 * Content Reading Level Too High Diagnostic
 *
 * Detects when content is too complex for general audiences.
 *
 * @since   1.26033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Reading Level Too High Diagnostic Class
 *
 * 54% of US adults read at or below 8th grade level. Complex content (grade 13+)
 * excludes half your potential audience.
 *
 * @since 1.26033.1645
 */
class Diagnostic_Content_Reading_Level_Too_High extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-reading-level-too-high';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Reading Level Too High';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect when content is too complex for general audiences (grade 8-10 target)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check average reading level
		$reading_level = apply_filters( 'wpshadow_average_reading_level_grade', 0 );
		if ( $reading_level > 11 && $reading_level > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: grade level */
				__( 'Average reading level is grade %d; 54%% of US adults read at grade 8 or below, you\'re excluding audience', 'wpshadow' ),
				$reading_level
			);
		}

		// Check if specialized language is necessary
		$technical_required = apply_filters( 'wpshadow_technical_language_necessary', false );
		if ( ! $technical_required && $reading_level > 11 ) {
			$issues[] = __( 'Content uses complex language when simpler alternatives would work', 'wpshadow' );
		}

		// Check for jargon clarity
		$jargon_explained = apply_filters( 'wpshadow_jargon_is_explained', false );
		if ( ! $jargon_explained ) {
			$issues[] = __( 'Complex terms should be defined or linked to glossary entries', 'wpshadow' );
		}

		// Check for paragraph length
		$paragraphs_long = apply_filters( 'wpshadow_has_excessively_long_paragraphs', false );
		if ( $paragraphs_long ) {
			$issues[] = __( 'Break long paragraphs (10+ lines) into smaller chunks for readability', 'wpshadow' );
		}

		// Check for sentence complexity
		$sentences_long = apply_filters( 'wpshadow_has_very_complex_sentences', false );
		if ( $sentences_long ) {
			$issues[] = __( 'Complex sentences (30+ words) reduce readability; use shorter, simpler sentences', 'wpshadow' );
		}

		// Check for accessibility impact
		$accessibility_impact = apply_filters( 'wpshadow_complex_language_accessibility_barrier', false );
		if ( $accessibility_impact ) {
			$issues[] = __( 'Complex language creates accessibility barrier for dyslexic and ESL readers', 'wpshadow' );
		}

		// Check for bounce rate impact
		$bounce_rate = apply_filters( 'wpshadow_high_bounce_rate_reading_difficulty', false );
		if ( $bounce_rate ) {
			$issues[] = __( 'Complex content has higher bounce rates; simpler writing increases engagement', 'wpshadow' );
		}

		// Check for target audience clarification
		$audience_intended = apply_filters( 'wpshadow_intended_audience_is_expert_only', false );
		if ( ! $audience_intended ) {
			$issues[] = __( 'Simplify language to reach broader audience; can always link to advanced resources', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-reading-level-too-high',
			);
		}

		return null;
	}
}
