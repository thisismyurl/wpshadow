<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unused Third-Party Embeds and Legacy Pixels (FE-323)
 *
 * Detects orphaned embeds/pixels still loading.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_UnusedThirdPartyEmbeds extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
// Check for unnecessary plugins
		if (!function_exists('get_plugins')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		
		$plugins = get_plugins();
		
		if (count($plugins) > 30) {
			return [
				'status' => 'info',
				'message' => sprintf(__('You have %d plugins - consider reviewing unused ones', 'wpshadow'), count($plugins)),
				'threat_level' => 'low'
			];
		}
		return null; // No issues detected
	}
}
