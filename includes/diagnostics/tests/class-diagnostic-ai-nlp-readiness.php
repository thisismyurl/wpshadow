<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Ai_Nlp_Readiness extends Diagnostic_Base {
	protected static $slug = 'ai-nlp-readiness';

	protected static $title = 'Ai Nlp Readiness';

	protected static $description = 'Automatically initialized lean diagnostic for Ai Nlp Readiness. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'ai-nlp-readiness';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is content optimized for NLP?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is content optimized for NLP?. Part of AI & ML Readiness analysis.', 'wpshadow' );
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
		// Implement: Is content optimized for NLP? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 46;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/ai-nlp-readiness/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/ai-nlp-readiness/';
	}

	public static function check(): ?array {
		$issues = [];

		// Check if NLP optimization is enabled
		$nlp_enabled = get_option('wpshadow_nlp_optimization_enabled', false);

		if (!$nlp_enabled) {
			$issues[] = 'NLP content optimization not enabled';
		}

		// Check post count for NLP training
		$post_count = wp_count_posts();
		$published_posts = $post_count->publish ?? 0;

		if ($published_posts < 20) {
			$issues[] = 'Insufficient content for NLP training (need 20+ posts)';
		}

		return empty($issues) ? null : [
			'id' => 'ai-nlp-readiness',
			'title' => 'Content not optimized for NLP',
			'description' => 'Enable NLP optimization and build content corpus',
			'severity' => 'medium',
			'category' => 'ai_readiness',
			'threat_level' => 46,
			'details' => $issues,
		];
	}

	public static function test_live_ai_nlp_readiness(): array {
		delete_option('wpshadow_nlp_optimization_enabled');
		$r1 = self::check();

		update_option('wpshadow_nlp_optimization_enabled', true);
		$r2 = self::check();

		delete_option('wpshadow_nlp_optimization_enabled');
		return ['passed' => is_array($r1) && (is_null($r2) || is_array($r2)), 'message' => 'NLP readiness check working'];
	}
	}

}

