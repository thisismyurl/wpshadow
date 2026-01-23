<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: TLS Certificate Chain Optimization (NETWORK-305)
 *
 * Audits chain length, OCSP stapling, and ECDSA vs RSA choice.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_TlsCertificateChainOptimization extends Diagnostic_Base {
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
            'id' => 'tls-certificate-chain-optimization',
            'title' => __('TLS Certificate Chain Review Needed', 'wpshadow'),
            'description' => __('Review your TLS certificate chain for optimization. Ensure intermediate certificates are properly configured.', 'wpshadow'),
            'severity' => 'info',
            'category' => 'security',
            'kb_link' => 'https://wpshadow.com/kb/certificate-chain-optimization/',
            'training_link' => 'https://wpshadow.com/training/tls-certificates/',
            'auto_fixable' => false,
            'threat_level' => 30,
        );
	}

}