<?php
/**
 * Braintree Tokenization Security Diagnostic
 *
 * Braintree Tokenization Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1406.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Braintree Tokenization Security Diagnostic Class
 *
 * @since 1.1406.0000
 */
class Diagnostic_BraintreeTokenizationSecurity extends Diagnostic_Base {

	protected static $slug = 'braintree-tokenization-security';
	protected static $title = 'Braintree Tokenization Security';
	protected static $description = 'Braintree Tokenization Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		$issues = array();
		
		// Check 1: Token encryption enabled
		$token_encryption = get_option( 'braintree_token_encryption', false );
		if ( ! $token_encryption ) {
			$issues[] = 'Token encryption disabled';
		}
		
		// Check 2: Vault feature enabled
		$vault_enabled = get_option( 'braintree_vault_enabled', false );
		if ( ! $vault_enabled ) {
			$issues[] = 'Vault feature disabled';
		}
		
		// Check 3: SSL required for tokenization
		if ( ! is_ssl() ) {
			$issues[] = 'SSL not enabled for tokenization';
		}
		
		// Check 4: Token refresh configured
		$token_refresh = get_option( 'braintree_token_refresh_enabled', false );
		if ( ! $token_refresh ) {
			$issues[] = 'Token refresh not configured';
		}
		
		// Check 5: PCI compliance mode
		$pci_mode = get_option( 'braintree_pci_compliance_mode', 'standard' );
		if ( 'standard' !== $pci_mode ) {
			$issues[] = 'PCI compliance mode not standard';
		}
		
		// Check 6: Secure token storage
		$secure_storage = get_option( 'braintree_secure_token_storage', false );
		if ( ! $secure_storage ) {
			$issues[] = 'Secure token storage disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 65 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Braintree tokenization security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/braintree-tokenization-security',
			);
		}
		
		return null;
	}
}
