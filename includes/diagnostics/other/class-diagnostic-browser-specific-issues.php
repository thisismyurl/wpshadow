<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Browser-Specific Performance Issues (RUM-004)
 * 
 * Identifies performance problems affecting specific browsers.
 * Philosophy: Educate (#5) - Fix browser compatibility issues.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Browser_Specific_Issues extends Diagnostic_Base {
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		$issue_count = (int) get_transient('wpshadow_browser_issue_count');
		$affected_browser = get_transient('wpshadow_most_affected_browser');

		if ($issue_count > 0) {
			return array(
				'id' => 'browser-specific-issues',
				'title' => sprintf(__('Browser-specific issues detected (%d)', 'wpshadow'), $issue_count),
				'description' => __('Certain browsers are experiencing degraded performance or compatibility issues. Test affected browsers and apply targeted fixes.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'other',
				'kb_link' => 'https://wpshadow.com/kb/browser-specific-issues/',
				'training_link' => 'https://wpshadow.com/training/cross-browser-performance/',
				'auto_fixable' => false,
				'threat_level' => 45,
				'affected_browser' => $affected_browser,
			);
		}

		return null;
	}
    }
