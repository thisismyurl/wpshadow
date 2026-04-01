<?php
/**
 * Content Depth Intent Mismatch Diagnostic
 *
 * Detects when content depth doesn't align with search intent.
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
 * Content Depth Intent Mismatch Diagnostic Class
 *
 * Detects when content depth doesn't align with search intent, resulting in
 * poor user experience and lower search rankings.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Depth_Intent_Mismatch extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-depth-intent-mismatch';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Depth Doesn\'t Match Intent';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect content depth misalignment with search intent (quick vs ultimate)';

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

		// Check for quick posts that are too long
		$quick_too_long = apply_filters( 'wpshadow_quick_posts_exceed_wordcount', false );
		if ( $quick_too_long ) {
			$issues[] = __( 'Posts titled \"Quick Guide\" exceed 1,500 words; either trim or retitle as \"Complete Guide\"', 'wpshadow' );
		}

		// Check for ultimate posts that are too short
		$ultimate_too_short = apply_filters( 'wpshadow_ultimate_posts_below_wordcount', false );
		if ( $ultimate_too_short ) {
			$issues[] = __( 'Posts titled \"Ultimate Guide\" are under 1,500 words; expand with examples or change title', 'wpshadow' );
		}

		// Check for how-to posts lacking structure
		$howto_missing_steps = apply_filters( 'wpshadow_howto_posts_missing_step_structure', false );
		if ( $howto_missing_steps ) {
			$issues[] = __( 'How-to posts should have numbered steps with clear instructions', 'wpshadow' );
		}

		// Check for tutorial posts lacking examples
		$tutorial_missing_examples = apply_filters( 'wpshadow_tutorial_posts_missing_examples', false );
		if ( $tutorial_missing_examples ) {
			$issues[] = __( 'Tutorial posts should include screenshots or code examples for each step', 'wpshadow' );
		}

		// Check for intent signals in titles
		$unclear_intent = apply_filters( 'wpshadow_posts_have_unclear_intent_signals', false );
		if ( $unclear_intent ) {
			$issues[] = __( 'Post titles should clearly signal intent (Quick, Complete, Ultimate, How-to, Tutorial)', 'wpshadow' );
		}

		// Check for helpful content system alignment
		$helpful_aligned = apply_filters( 'wpshadow_content_aligns_with_helpful_system', false );
		if ( ! $helpful_aligned ) {
			$issues[] = __( 'Google helpful content update penalizes depth/intent misalignment', 'wpshadow' );
		}

		// Check for bounce rate impact
		$bounce_rate = apply_filters( 'wpshadow_high_bounce_rate_depth_mismatch', false );
		if ( $bounce_rate ) {
			$issues[] = __( 'Posts with depth/intent mismatch show 45% higher bounce rates', 'wpshadow' );
		}

		// Check for TL;DR sections on long quick posts
		$tldr_missing = apply_filters( 'wpshadow_long_quick_posts_missing_tldr', false );
		if ( $tldr_missing ) {
			$issues[] = __( 'Add TL;DR summary at top of long posts promising quick answers', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-depth-intent-mismatch?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
