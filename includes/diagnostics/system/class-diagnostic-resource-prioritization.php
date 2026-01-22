<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Resource Prioritization Strategy (ASSET-024)
 * 
 * Analyzes use of fetchpriority and preload for critical resources.
 * Philosophy: Show value (#9) - Browser loads what matters first.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Resource_Prioritization extends Diagnostic_Base {
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		$preload_count = (int) get_transient('wpshadow_critical_preload_count');
		$fetchpriority_count = (int) get_transient('wpshadow_fetchpriority_usage');

		if ($preload_count === 0 || $fetchpriority_count === 0) {
			return array(
				'id' => 'resource-prioritization',
				'title' => __('Critical resources not prioritized', 'wpshadow'),
				'description' => __('Use preload and fetchpriority on hero images, above-the-fold CSS, and critical JS to improve LCP.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'system',
				'kb_link' => 'https://wpshadow.com/kb/resource-prioritization/',
				'training_link' => 'https://wpshadow.com/training/resource-hints/',
				'auto_fixable' => false,
				'threat_level' => 50,
			);
		}

		return null;
	}
    }
