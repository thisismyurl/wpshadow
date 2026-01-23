<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: JavaScript Asset Versions
 * Checks for version query strings (?ver=) on JavaScript files that can be removed
 */
class Test_Performance_Asset_Versions_JS extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		if (!did_action('wp_enqueue_scripts') && !did_action('admin_enqueue_scripts')) {
			return null;
		}

		global $wp_scripts;

		if (!isset($wp_scripts) || !($wp_scripts instanceof \WP_Scripts)) {
			return null;
		}

		$versioned_assets = 0;
		foreach ($wp_scripts->registered as $handle => $script) {
			if (is_string($script->src) && strpos($script->src, '?ver=') !== false) {
				$versioned_assets++;
			}
		}

		if ($versioned_assets === 0) {
			return null;
		}

		return array(
			'id'            => 'asset-versions-js',
			'title'         => 'JavaScript Asset Version Strings Found',
			'threat_level'  => 7,
			'description'   => sprintf(
				'Found %d JavaScript files with version query strings (?ver=) that could be removed.',
				$versioned_assets
			),
		);
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_asset_versions_js(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'JS asset versions are clean' : 'JS version query strings detected',
		);
	}
}
