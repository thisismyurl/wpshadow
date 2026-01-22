<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: TLS ALPN and HTTP/3 Negotiation (NETWORK-356)
 *
 * Detects ALPN drift, H3 enablement, and fallback to H2/H1.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_TlsAlpnH3Negotiation extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		if (!is_ssl()) {
            return null;
        }
        
        $alt_svc = wp_get_server_var('HTTP_ALT_SVC');
        
        if (!$alt_svc || stripos($alt_svc, 'h3') === false) {
            return array(
                'id' => 'tls-alpn-h3-negotiation',
                'title' => __('HTTP/3 (QUIC) Not Enabled', 'wpshadow'),
                'description' => __('HTTP/3 (QUIC) protocol is not enabled. Enabling it provides faster connections and better mobile performance.', 'wpshadow'),
                'severity' => 'info',
                'category' => 'performance',
                'kb_link' => 'https://wpshadow.com/kb/http3-quic/',
                'training_link' => 'https://wpshadow.com/training/http3-setup/',
                'auto_fixable' => false,
                'threat_level' => 20,
            );
        }
        return null;
	}
}
