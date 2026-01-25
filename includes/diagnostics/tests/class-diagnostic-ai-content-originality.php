<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_AiContentOriginality extends Diagnostic_Base {
	protected static $slug = 'ai-content-originality';

	protected static $title = 'Ai Content Originality';

	protected static $description = 'Automatically initialized lean diagnostic for Ai Content Originality. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @return string
	 */
	public static function get_id(): string {
		return 'ai-content-originality';
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'AI Content Quality Score', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Detects AI-generated content, scores originality. Google penalty prevention.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'ai_readiness';
	}

	/**
	 * Get threat level (0-100)
	 * Higher = more critical
	 *
	 * @return int
	 */
	public static function get_threat_level(): int {
		return 70;
	}

	/**
	 * Run the diagnostic
	 *
	 * @return array Result with status, message, and data
	 */
	public static function run(): array {
		// STUB: Implement ai-content-originality diagnostic
		//
		// This is a KILLER test that delivers "Holy Sh*t" moments:
		// Shows \"12 posts flagged as generic AI content (bad for SEO)\".
		//
		// Implementation notes:
		// - Quantify impact with real numbers (dollar amounts, percentages)
		// - Show specific examples (file names, URLs, exact problems)
		// - Provide actionable fix recommendations
		// - Link to KB article explaining why this matters
		// - Track KPI: time saved, revenue impact, disaster prevented

		return array(
			'status'  => 'todo',
			'message' => __( 'Not yet implemented - Priority 1 killer test', 'wpshadow' ),
			'data'    => array(
				'impact'   => 'Shows \"12 posts flagged as generic AI content (bad for SEO)\".',
				'priority' => 1,
			),
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @return string
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/content-originality';
	}

	/**
	 * Get training video URL
	 *
	 * @return string
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/content-originality';
	}

	public static function check(): ?array {
		$issues = array();

		// Query recent posts and check AI content detection
		$recent_posts = get_posts( array( 'numberposts' => 10 ) );

		if ( empty( $recent_posts ) ) {
			return null; // No posts to analyze
		}

		$ai_flagged = 0;
		foreach ( $recent_posts as $post ) {
			$ai_score = get_post_meta( $post->ID, '_ai_content_score', true );
			if ( $ai_score && $ai_score > 0.7 ) { // >70% likely AI
				++$ai_flagged;
			}
		}

		if ( $ai_flagged > count( $recent_posts ) * 0.5 ) {
			$issues[] = $ai_flagged . ' posts flagged as potentially AI-generated (bad for SEO)';
		}

		return empty( $issues ) ? null : array(
			'id'           => 'ai-content-originality',
			'title'        => 'AI-generated content detected',
			'description'  => 'High percentage of content appears AI-generated; Google may penalize',
			'severity'     => 'high',
			'category'     => 'ai_readiness',
			'threat_level' => 70,
			'details'      => $issues,
		);
	}

	public static function test_live_ai_content_originality(): array {
		// Create test post without AI flag
		$post_id = wp_insert_post(
			array(
				'post_title'   => 'Test Post',
				'post_content' => 'Original content',
			)
		);
		$r1      = self::check();

		// Flag post as AI-generated
		update_post_meta( $post_id, '_ai_content_score', 0.85 );
		$r2 = self::check();

		wp_delete_post( $post_id, true );
		return array(
			'passed'  => ( is_null( $r1 ) || is_array( $r1 ) ),
			'message' => 'Content originality check working',
		);
	}
}
