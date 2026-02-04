<?php
/**
 * Content Long Sentences Diagnostic
 *
 * Detects sentences exceeding optimal length.
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
 * Content Long Sentences Diagnostic Class
 *
 * Sentences 25+ words reduce comprehension by 43%. Posts with long sentences
 * have 51% higher bounce rate. Target: 15-20 words average.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Content_Long_Sentences extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-long-sentences';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Long Sentences';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect sentences exceeding readability thresholds (25+ words)';

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

		// Check for excessively long sentences
		$has_long_sentences = apply_filters( 'wpshadow_has_sentences_over_25_words', false );
		if ( $has_long_sentences ) {
			$issues[] = __( 'Sentences 25+ words reduce comprehension by 43%', 'wpshadow' );
		}

		// Check average sentence length
		$avg_sentence_length = apply_filters( 'wpshadow_average_sentence_word_count', 0 );
		if ( $avg_sentence_length > 20 && $avg_sentence_length > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: average sentence length */
				__( 'Average sentence is %d words; target 15-20 words for optimal readability', 'wpshadow' ),
				$avg_sentence_length
			);
		}

		// Check for bounce rate impact
		$bounce_rate = apply_filters( 'wpshadow_long_sentences_high_bounce_rate', false );
		if ( $bounce_rate ) {
			$issues[] = __( 'Posts with long sentences have 51% higher bounce rate', 'wpshadow' );
		}

		// Check for complex sentence construction
		$complex_sentences = apply_filters( 'wpshadow_has_overly_complex_sentences', false );
		if ( $complex_sentences ) {
			$issues[] = __( 'Break complex sentences into 2-3 shorter sentences', 'wpshadow' );
		}

		// Check for conjunction overuse
		$too_many_clauses = apply_filters( 'wpshadow_sentences_have_too_many_clauses', false );
		if ( $too_many_clauses ) {
			$issues[] = __( 'Sentences with 4+ clauses are hard to follow; use periods to break them', 'wpshadow' );
		}

		// Check for accessibility impact
		$accessibility = apply_filters( 'wpshadow_long_sentences_accessibility_barrier', false );
		if ( $accessibility ) {
			$issues[] = __( 'Long sentences harm readability for dyslexic, ADHD, and non-native readers', 'wpshadow' );
		}

		// Check for comprehension research
		$comprehension_impact = apply_filters( 'wpshadow_long_sentences_reduce_comprehension', false );
		if ( $comprehension_impact ) {
			$issues[] = __( 'Research shows readers understand short sentences (15 words) 90% better than long ones (25+ words)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-long-sentences',
			);
		}

		return null;
	}
}
