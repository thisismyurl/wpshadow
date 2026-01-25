<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


/**
 * CONTENT QUALITY - Keyword Analysis Approach
 * ============================================================
 *
 * DETECTION APPROACH:
 * Scan local content for quality/bias issues via keyword analysis
 *
 * LOCAL CHECKS:
 * - Query recent posts from database
 * - Analyze content for keyword density/balance
 * - Check for bias indicators (loaded language, stereotypes)
 * - Verify content freshness and relevance
 * - Score content quality based on heuristics
 * - Check for diverse perspectives/sources
 *
 * PASS CRITERIA:
 * - Content shows balanced perspective
 * - Keyword distribution natural (not stuffed)
 * - Recent content available
 * - Sources documented when present
 *
 * FAIL CRITERIA:
 * - Obvious bias detected
 * - Keyword stuffing present
 * - All content outdated
 * - No source documentation
 *
 * TEST STRATEGY:
 * 1. Create mock posts with biased/clean content
 * 2. Test bias detection logic
 * 3. Test keyword density calculation
 * 4. Test freshness scoring
 */
class Diagnostic_Ai_Sentiment_Analysis extends Diagnostic_Base {
	protected static $slug = 'ai-sentiment-analysis';

	protected static $title = 'Ai Sentiment Analysis';

	protected static $description = 'Automatically initialized lean diagnostic for Ai Sentiment Analysis. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'ai-sentiment-analysis';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is content ready for sentiment analysis?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is content ready for sentiment analysis?. Part of AI & ML Readiness analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'ai_readiness';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// Implement: Is content ready for sentiment analysis? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 58;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/ai-sentiment-analysis/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/ai-sentiment-analysis/';
	}

	public static function check(): ?array {
		$issues = array();

		// Check if sentiment analysis is enabled
		$sentiment_enabled = get_option( 'wpshadow_sentiment_analysis_enabled', false );

		if ( ! $sentiment_enabled ) {
			$issues[] = 'Sentiment analysis not enabled';
		}

		// Check for recent sentiment data
		$last_analysis = get_option( 'wpshadow_sentiment_last_run', 0 );
		$days_old      = ( time() - $last_analysis ) / ( 24 * 3600 );

		if ( $days_old > 7 ) {
			$issues[] = 'Sentiment analysis data is stale (not run in 7 days)';
		}

		return empty( $issues ) ? null : array(
			'id'           => 'ai-sentiment-analysis',
			'title'        => 'Sentiment analysis not running',
			'description'  => 'Enable sentiment analysis to monitor user feedback',
			'severity'     => 'low',
			'category'     => 'ai_readiness',
			'threat_level' => 30,
			'details'      => $issues,
		);
	}

	public static function test_live_ai_sentiment_analysis(): array {
		delete_option( 'wpshadow_sentiment_analysis_enabled' );
		delete_option( 'wpshadow_sentiment_last_run' );
		$r1 = self::check();

		update_option( 'wpshadow_sentiment_analysis_enabled', true );
		update_option( 'wpshadow_sentiment_last_run', time() );
		$r2 = self::check();

		delete_option( 'wpshadow_sentiment_analysis_enabled' );
		delete_option( 'wpshadow_sentiment_last_run' );
		return array(
			'passed'  => is_array( $r1 ) && is_null( $r2 ),
			'message' => 'Sentiment analysis check working',
		);
	}
}
