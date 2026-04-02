<?php
/**
 * PCI DSS Compliance Diagnostic
 *
 * Checks if payment processing meets PCI DSS standards.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PCI DSS Compliance Diagnostic Class
 *
 * Verifies that payment processing complies with PCI DSS standards
 * and that credit card data is handled securely.
 *
 * @since 1.6093.1200
 */
class Diagnostic_PCI_DSS_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pci-dss-compliance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PCI DSS Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if payment processing meets PCI DSS standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the PCI DSS compliance diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if PCI DSS issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check 1: SSL/TLS encryption.
		$ssl_enabled = is_ssl();
		$stats['ssl_enabled'] = $ssl_enabled;

		if ( ! $ssl_enabled ) {
			$issues[] = __( 'SSL/TLS not enabled - PCI DSS Requirement 4 violation', 'wpshadow' );
		}

		// Check 2: Firewall configuration.
		$has_firewall = get_option( 'woocommerce_firewall_enabled' );
		$stats['firewall_enabled'] = boolval( $has_firewall );

		if ( ! $has_firewall ) {
			$warnings[] = __( 'Firewall not explicitly enabled - PCI DSS Requirement 1', 'wpshadow' );
		}

		// Check 3: Strong access control.
		$strong_passwords_required = get_option( 'woocommerce_strong_passwords' );
		$stats['strong_password_requirement'] = boolval( $strong_passwords_required );

		if ( ! $strong_passwords_required ) {
			$warnings[] = __( 'Strong password requirement not enabled - PCI DSS Requirement 8', 'wpshadow' );
		}

		// Check 4: No local credit card storage.
		$card_storage = get_option( 'woocommerce_store_credit_card_locally' );
		$stats['local_card_storage'] = boolval( $card_storage );

		if ( $card_storage ) {
			$issues[] = __( 'Credit cards stored locally - PCI DSS Requirement 3 violation', 'wpshadow' );
		}

		// Check 5: Payment gateway compliance.
		$payment_gateways = array();
		if ( function_exists( 'WC' ) ) {
			$woocommerce = WC();
			if ( is_object( $woocommerce ) && method_exists( $woocommerce, 'payment_gateways' ) ) {
				$gateways_manager = $woocommerce->payment_gateways();
				if ( is_object( $gateways_manager ) && method_exists( $gateways_manager, 'payment_gateways' ) ) {
					$payment_gateways = $gateways_manager->payment_gateways();
				}
			}
		}
		$compliant_gateways = array();

		$pci_compliant_gateways = array(
			'stripe',
			'paypal',
			'authorize',
			'square',
		);

		foreach ( $payment_gateways as $gateway ) {
			if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled && isset( $gateway->id ) ) {
				foreach ( $pci_compliant_gateways as $compliant ) {
					if ( false !== strpos( strtolower( (string) $gateway->id ), $compliant ) ) {
						$compliant_gateways[] = isset( $gateway->title ) ? $gateway->title : $gateway->id;
					}
				}
			}
		}

		$stats['pci_compliant_gateways'] = $compliant_gateways;

		if ( empty( $compliant_gateways ) ) {
			$issues[] = __( 'No PCI-compliant payment gateway detected', 'wpshadow' );
		}

		// Check 6: Regular security updates.
		global $wp_version;
		$wp_version_current = version_compare( $wp_version, '6.0', '>=' );
		$stats['wordpress_current'] = $wp_version_current;

		if ( ! $wp_version_current ) {
			$warnings[] = sprintf(
				/* translators: %s: version */
				__( 'WordPress version %s outdated - update for security patches', 'wpshadow' ),
				$wp_version
			);
		}

		// Check PHP version.
		$php_version = phpversion();
		$php_current = version_compare( $php_version, '8.0', '>=' );
		$stats['php_current'] = $php_current;

		if ( ! $php_current ) {
			$warnings[] = sprintf(
				/* translators: %s: version */
				__( 'PHP version %s outdated - update to 8.0+ for security', 'wpshadow' ),
				$php_version
			);
		}

		// Check 7: Access control and logging.
		$logging_enabled = get_option( 'woocommerce_activity_logging_enabled' );
		$stats['activity_logging'] = boolval( $logging_enabled );

		if ( ! $logging_enabled ) {
			$warnings[] = __( 'Activity logging not enabled - PCI DSS Requirement 10', 'wpshadow' );
		}

		// Check 8: Vulnerability scanning.
		$vulnerability_scanning = get_option( 'woocommerce_vulnerability_scanning' );
		$stats['vulnerability_scanning'] = boolval( $vulnerability_scanning );

		if ( ! $vulnerability_scanning ) {
			$warnings[] = __( 'Regular vulnerability scanning not enabled', 'wpshadow' );
		}

		// Check 9: PCI compliance certificate.
		$pci_certificate = get_option( 'woocommerce_pci_certificate_date' );
		$stats['pci_certificate'] = ! empty( $pci_certificate ) ? 'Active' : 'Not documented';

		if ( empty( $pci_certificate ) ) {
			$warnings[] = __( 'PCI DSS compliance certificate not documented', 'wpshadow' );
		}

		// Check 10: Two-factor authentication for admin.
		$twofa_enabled = get_option( 'woocommerce_twofa_enabled' );
		$stats['2fa_admin'] = boolval( $twofa_enabled );

		if ( ! $twofa_enabled ) {
			$warnings[] = __( 'Two-factor authentication for admin not enabled', 'wpshadow' );
		}

		// Check 11: Regular penetration testing.
		$penetration_testing = get_option( 'woocommerce_penetration_testing_date' );
		$stats['penetration_testing'] = ! empty( $penetration_testing ) ? 'Done' : 'Not scheduled';

		if ( empty( $penetration_testing ) ) {
			$warnings[] = __( 'Regular penetration testing not scheduled', 'wpshadow' );
		}

		// Check for security plugins.
		$security_plugins = array(
			'wordfence-security/wordfence.php',
			'all-in-one-wp-security-and-firewall/all_in_one_wp_security_and_firewall.php',
			'sucuri-scanner/sucuri.php',
		);

		$has_security_plugin = false;
		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_security_plugin = true;
				break;
			}
		}

		$stats['security_plugin'] = $has_security_plugin;

		if ( ! $has_security_plugin ) {
			$warnings[] = __( 'No security plugin active', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'PCI DSS compliance has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pci-dss-compliance',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'PCI DSS compliance has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pci-dss-compliance',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // PCI DSS compliance is good.
	}
}
