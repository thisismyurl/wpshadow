<?php
/**
 * Customer Account Security Standards Diagnostic
 *
 * Verifies customer accounts have proper security measures
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Ecommerce;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_CustomerAccountSecurity Class
 *
 * Checks for 2FA support, password policies, login rate limiting
 *
 * @since 1.6093.1200
 */
class Diagnostic_CustomerAccountSecurity extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'customer-account-security';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'Customer Account Security Standards';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies customer accounts have proper security measures';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'ecommerce';

/**
 * Run the diagnostic check.
 *
 * @since 1.6093.1200
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check for ecommerce plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$ecommerce_plugins = array( 'woocommerce', 'easy-digital-downloads', 'edd' );
		$has_ecommerce = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $ecommerce_plugins as $ec_plugin ) {
				if ( stripos( $plugin, $ec_plugin ) !== false ) {
					$has_ecommerce = true;
					break 2;
				}
			}
		}

		if ( ! $has_ecommerce ) {
			return null;
		}

		$issues = array();

		// Check for 2FA plugins.
		$twofa_plugins = array( 'two-factor', '2fa', 'google-authenticator', 'wordfence' );
		$has_2fa = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $twofa_plugins as $tfa_plugin ) {
				if ( stripos( $plugin, $tfa_plugin ) !== false ) {
					$has_2fa = true;
					break 2;
				}
			}
		}

		if ( ! $has_2fa ) {
			$issues[] = __( 'No two-factor authentication plugin for customer accounts', 'wpshadow' );
		}

		// Check password strength requirements.
		$password_plugins = array( 'password-policy', 'force-strong-passwords', 'woocommerce-password' );
		$has_password_policy = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $password_plugins as $pass_plugin ) {
				if ( stripos( $plugin, $pass_plugin ) !== false ) {
					$has_password_policy = true;
					break 2;
				}
			}
		}

		if ( ! $has_password_policy ) {
			$issues[] = __( 'No password strength enforcement plugin detected', 'wpshadow' );
		}

		// Check for account activity monitoring.
		$activity_plugins = array( 'activity-log', 'wp-security-audit-log', 'simple-history' );
		$has_activity = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $activity_plugins as $act_plugin ) {
				if ( stripos( $plugin, $act_plugin ) !== false ) {
					$has_activity = true;
					break 2;
				}
			}
		}

		if ( ! $has_activity ) {
			$issues[] = __( 'No account activity monitoring plugin detected', 'wpshadow' );
		}

		// Check for login attempt limiting.
		$limit_plugins = array( 'limit-login', 'loginizer', 'wordfence' );
		$has_limit = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $limit_plugins as $lim_plugin ) {
				if ( stripos( $plugin, $lim_plugin ) !== false ) {
					$has_limit = true;
					break 2;
				}
			}
		}

		if ( ! $has_limit ) {
			$issues[] = __( 'No login attempt limiting plugin detected', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Customer account security concerns: %s. Ecommerce sites need enhanced account protection.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/customer-account-security',
		);
	}
}
