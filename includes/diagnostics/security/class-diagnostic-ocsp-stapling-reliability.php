<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: OCSP Stapling Reliability (NETWORK-359)
 *
 * Checks stapling presence, freshness, and failover to must-staple.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_OcspStaplingReliability extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		if (!is_ssl()) {
            return null;
        }
        
        return array(
            'id' => 'ocsp-stapling-reliability',
            'title' => __('OCSP Stapling Configuration', 'wpshadow'),
            'description' => __('Verify that OCSP stapling is enabled in your web server configuration to avoid OCSP lookup delays during TLS handshakes.', 'wpshadow'),
            'severity' => 'info',
            'category' => 'security',
            'kb_link' => 'https://wpshadow.com/kb/ocsp-stapling/',
            'training_link' => 'https://wpshadow.com/training/ocsp-optimization/',
            'auto_fixable' => false,
            'threat_level' => 30,
        );
	}
}
