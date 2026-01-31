<?php
/**
 * Braintree 3d Secure Implementation Diagnostic
 *
 * Braintree 3d Secure Implementation vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1407.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Braintree 3d Secure Implementation Diagnostic Class
 *
 * @since 1.1407.0000
 */
class Diagnostic_Braintree3dSecureImplementation extends Diagnostic_Base {

	protected static $slug = 'braintree-3d-secure-implementation';
	protected static $title = 'Braintree 3d Secure Implementation';
	protected static $description = 'Braintree 3d Secure Implementation vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Braintree' ) && ! defined( 'WC_BRAINTREE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: 3D Secure enabled.
		$three_d_secure = get_option( 'wc_braintree_3d_secure_enabled', '0' );
		if ( '0' === $three_d_secure ) {
			$issues[] = '3D Secure not enabled';
		}
		
		// Check 2: SSL enforcement.
		if ( ! is_ssl() ) {
			$issues[] = 'payments without HTTPS';
		}
		
		// Check 3: Sandbox mode.
		$sandbox = get_option( 'wc_braintree_sandbox', '0' );
		if ( '1' === $sandbox ) {
			$issues[] = 'sandbox mode on live site';
		}
		
		// Check 4: Fraud tools.
		$fraud_tools = get_option( 'wc_braintree_fraud_tools', '1' );
		if ( '0' === $fraud_tools ) {
			$issues[] = 'fraud tools disabled';
		}
		
		// Check 5: Transaction logging.
		$logging = get_option( 'wc_braintree_logging', '1' );
		if ( '0' === $logging ) {
			$issues[] = 'transaction logging disabled';
		}
		
		// Check 6: Tokenization.
		$tokenization = get_option( 'wc_braintree_tokenization', '1' );
		if ( '0' === $tokenization ) {
			$issues[] = 'card tokenization disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 75 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Braintree security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/braintree-3d-secure-implementation',
			);
		}
		
		return null;
	}
}
