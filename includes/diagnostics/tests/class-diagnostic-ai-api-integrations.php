<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Ai_Api_Integrations extends Diagnostic_Base {
	protected static $slug = 'ai-api-integrations';

	protected static $title = 'Ai Api Integrations';

	protected static $description = 'Automatically initialized lean diagnostic for Ai Api Integrations. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'ai-api-integrations';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is AI API strategy documented?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is AI API strategy documented?. Part of AI & ML Readiness analysis.', 'wpshadow' );
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
		// Implement: Is AI API strategy documented? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 53;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/ai-api-integrations/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/ai-api-integrations/';
	}

	public static function check(): ?array {
		$issues = [];

		// Check for AI API integrations
		$openai_key = get_option('wpshadow_openai_api_key');
		$anthropic_key = get_option('wpshadow_anthropic_api_key');
		$huggingface_key = get_option('wpshadow_huggingface_api_key');

		// Check if any AI plugins are active
		$ai_plugins = ['ai-engine', 'jetpack-ai', 'wordpress-ai-suite'];
		$plugin_active = false;
		foreach ($ai_plugins as $plugin) {
			if (defined('PLUGIN_' . strtoupper(str_replace('-', '_', $plugin)) . '_VERSION')) {
				$plugin_active = true;
				break;
			}
		}

		if (empty($openai_key) && empty($anthropic_key) && empty($huggingface_key) && !$plugin_active) {
			$issues[] = 'No AI API integrations configured or plugins active';
		}

		return empty($issues) ? null : [
			'id' => 'ai-api-integrations',
			'title' => 'AI API integrations not configured',
			'description' => 'No active AI API connections found',
			'severity' => 'low',
			'category' => 'ai_readiness',
			'threat_level' => 25,
			'details' => $issues,
		];
	}

	public static function test_live_ai_api_integrations(): array {
		// Test with no API keys
		delete_option('wpshadow_openai_api_key');
		$r1 = self::check();

		// Test with API key set
		update_option('wpshadow_openai_api_key', 'sk-test-key');
		$r2 = self::check();

		delete_option('wpshadow_openai_api_key');
		return ['passed' => is_array($r1) && is_null($r2), 'message' => 'AI API integration check working'];
	}
}

