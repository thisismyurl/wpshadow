<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WAF Rule Performance Impact (SEC-PERF-005)
 * 
 * WAF Rule Performance Impact diagnostic
 * Philosophy: Show value (#9) - Security without penalty.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_DiagnosticWafRulePerformance extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if WAF (Web Application Firewall) is configured
        $has_wordfence = class_exists('Wordfence\Controller\Controller');
        $has_cloudflare_waf = !empty(wp_get_server_var('CF_RAY'));
        $has_sucuri = !empty(wp_get_server_var('X_SUCURI_CACHE'));
        
        if (!$has_wordfence && !$has_cloudflare_waf && !$has_sucuri) {
            return array(
                'id' => 'waf-rule-performance',
                'title' => __('Web Application Firewall Not Active', 'wpshadow'),
                'description' => __('No WAF service detected. Consider using Wordfence, Cloudflare WAF, or similar to protect against common web attacks.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'security',
                'kb_link' => 'https://wpshadow.com/kb/web-application-firewall/',
                'training_link' => 'https://wpshadow.com/training/waf-setup/',
                'auto_fixable' => false,
                'threat_level' => 70,
            );
        }
        
        return null;
    }
}
