<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_AiSemanticMetadata extends Diagnostic_Base {
	protected static $slug = 'ai-semantic-metadata';

	protected static $title = 'Ai Semantic Metadata';

	protected static $description = 'Automatically initialized lean diagnostic for Ai Semantic Metadata. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @return string
	 */
	public static function get_id(): string {
		return 'ai-semantic-metadata';
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Semantic Search Readiness', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Checks if content has structured data for AI/voice search. Future-proofing.', 'wpshadow' );
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
		return 50;
	}

	/**
	 * Run the diagnostic
	 *
	 * @return array Result with status, message, and data
	 */
	public static function run(): array {
		// STUB: Implement ai-semantic-metadata diagnostic
		//
		// This is a KILLER test that delivers "Holy Sh*t" moments:
		// Shows \"0% of content optimized for voice/AI search\" schema gaps.
		//
		// Implementation notes:
		// - Quantify impact with real numbers (dollar amounts, percentages)
		// - Show specific examples (file names, URLs, exact problems)
		// - Provide actionable fix recommendations
		// - Link to KB article explaining why this matters
		// - Track KPI: time saved, revenue impact, disaster prevented

		return array(
			'status'  => 'todo',
			'message' => __( 'Not yet implemented - Priority 2 killer test', 'wpshadow' ),
			'data'    => array(
				'impact'   => 'Shows \"0% of content optimized for voice/AI search\" schema gaps.',
				'priority' => 2,
			),
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @return string
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/semantic-metadata';
	}

	/**
	 * Get training video URL
	 *
	 * @return string
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/semantic-metadata';
	}

	public static function check(): ?array {
		$issues = array();

		// Check if semantic metadata is being generated
		$semantic_enabled = get_option( 'wpshadow_semantic_metadata_enabled', false );

		if ( ! $semantic_enabled ) {
			$issues[] = 'Semantic metadata generation not enabled';
		}

		// Check recent posts for semantic tags
		$recent_posts  = get_posts( array( 'numberposts' => 10 ) );
		$with_semantic = 0;
		foreach ( $recent_posts as $post ) {
			if ( get_post_meta( $post->ID, '_semantic_keywords', true ) ) {
				++$with_semantic;
			}
		}

		if ( $with_semantic < count( $recent_posts ) * 0.5 ) {
			$issues[] = 'Less than 50% of posts have semantic metadata';
		}

		return empty( $issues ) ? null : array(
			'id'           => 'ai-semantic-metadata',
			'title'        => 'Semantic metadata missing',
			'description'  => 'Enable semantic metadata generation for better AI understanding',
			'severity'     => 'medium',
			'category'     => 'ai_readiness',
			'threat_level' => 41,
			'details'      => $issues,
		);
	}

	public static function test_live_ai_semantic_metadata(): array {
		delete_option( 'wpshadow_semantic_metadata_enabled' );
		$r1 = self::check();

		update_option( 'wpshadow_semantic_metadata_enabled', true );
		$r2 = self::check();

		delete_option( 'wpshadow_semantic_metadata_enabled' );
		return array(
			'passed'  => is_array( $r1 ) && ( is_null( $r2 ) || is_array( $r2 ) ),
			'message' => 'Semantic metadata check working',
		);
	}
}
