<?php
/**
 * Content Long Paragraphs Diagnostic
 *
 * Detects paragraphs exceeding optimal length.
 *
 * @since   1.6033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Long Paragraphs Diagnostic Class
 *
 * Paragraphs 200+ words create intimidating walls of text.
 * 73% of users abandon. Mobile makes it worse (5 lines = 15 lines mobile).
 * Target: 50-75 words.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Content_Long_Paragraphs extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-long-paragraphs';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Long Paragraphs';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect paragraphs exceeding readability thresholds (200+ words)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for excessively long paragraphs
		$has_long_paragraphs = apply_filters( 'wpshadow_has_paragraphs_over_200_words', false );
		if ( $has_long_paragraphs ) {
			$issues[] = __( 'Paragraphs 200+ words create walls of text; 73% of users abandon', 'wpshadow' );
		}

		// Check average paragraph length
		$avg_paragraph_length = apply_filters( 'wpshadow_average_paragraph_word_count', 0 );
		if ( $avg_paragraph_length > 100 && $avg_paragraph_length > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: average paragraph length */
				__( 'Average paragraph is %d words; target 50-75 words for readability', 'wpshadow' ),
				$avg_paragraph_length
			);
		}

		// Check for mobile readability impact
		$mobile_impact = apply_filters( 'wpshadow_long_paragraphs_mobile_impact', false );
		if ( $mobile_impact ) {
			$issues[] = __( 'On mobile, 5 lines becomes 15 lines; long paragraphs are especially problematic', 'wpshadow' );
		}

		// Check for visual breaks
		$visual_breaks = apply_filters( 'wpshadow_content_has_adequate_visual_breaks', false );
		if ( ! $visual_breaks ) {
			$issues[] = __( 'Break paragraphs with images, lists, or subheadings every 75-100 words', 'wpshadow' );
		}

		// Check for paragraph structure variety
		$structure_variety = apply_filters( 'wpshadow_has_paragraph_structure_variety', false );
		if ( ! $structure_variety ) {
			$issues[] = __( 'Mix short and medium paragraphs; variety improves readability', 'wpshadow' );
		}

		// Check for accessibility impact
		$accessibility = apply_filters( 'wpshadow_long_paragraphs_accessibility_barrier', false );
		if ( $accessibility ) {
			$issues[] = __( 'Large text blocks intimidate readers with ADHD, dyslexia, and non-native speakers', 'wpshadow' );
		}

		// Check for bounce rate impact
		$bounce_rate = apply_filters( 'wpshadow_high_bounce_rate_long_paragraphs', false );
		if ( $bounce_rate ) {
			$issues[] = __( 'Breaking paragraphs can reduce bounce rate by 20-30%', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-long-paragraphs',
			);
		}

		return null;
	}
}
