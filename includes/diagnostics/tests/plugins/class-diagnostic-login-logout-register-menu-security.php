<?php
/**
 * Login Logout Register Menu Security Diagnostic
 *
 * Login Logout Register Menu Security issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1230.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Logout Register Menu Security Diagnostic Class
 *
 * @since 1.1230.0000
 */
class Diagnostic_LoginLogoutRegisterMenuSecurity extends Diagnostic_Base {

	protected static $slug = 'login-logout-register-menu-security';
	protected static $title = 'Login Logout Register Menu Security';
	protected static $description = 'Login Logout Register Menu Security issue found';
	protected static $family = 'security';

	public static function check() {
		// Check for login menu plugins or custom login pages
		$has_login_menu = function_exists( 'wp_nav_menu_item_custom_fields' ) ||
		                  get_option( 'login_logout_register_menu', '' ) !== '';

		$issues = array();

		// Check 1: Login URLs in menu
		$menus = wp_get_nav_menus();
		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			if ( $items ) {
				foreach ( $items as $item ) {
					if ( strpos( $item->url, 'wp-login.php' ) !== false ) {
						$issues[] = __( 'wp-login.php exposed in menu (brute force target)', 'wpshadow' );
						break 2;
					}
				}
			}
		}

		// Check 2: Logout nonce
		$logout_nonce = get_option( 'logout_nonce_enabled', 'yes' );
		if ( 'no' === $logout_nonce ) {
			$issues[] = __( 'Logout without nonce (CSRF risk)', 'wpshadow' );
		}

		// Check 3: Registration in menu
		$allow_registration = get_option( 'users_can_register', 0 );
		if ( $allow_registration ) {
			foreach ( $menus as $menu ) {
				$items = wp_get_nav_menu_items( $menu->term_id );
				if ( $items ) {
					foreach ( $items as $item ) {
						if ( strpos( $item->url, 'wp-login.php?action=register' ) !== false ) {
							$issues[] = __( 'Public registration link (spam accounts)', 'wpshadow' );
							break 2;
						}
					}
				}
			}
		}

		// Check 4: Redirect after login
		$redirect = get_option( 'login_redirect_url', '' );
		if ( empty( $redirect ) ) {
			$issues[] = __( 'No login redirect (default admin access)', 'wpshadow' );
		}

		// Check 5: Logout redirect
		$logout_redirect = get_option( 'logout_redirect_url', '' );
		if ( empty( $logout_redirect ) ) {
			$issues[] = __( 'No logout redirect (session info visible)', 'wpshadow' );
		}

		// Check 6: Login attempt logging
		$log_attempts = get_option( 'log_login_attempts', 'no' );
		if ( 'no' === $log_attempts ) {
			$issues[] = __( 'Login attempts not logged (no audit trail)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 70;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Login/logout menu has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/login-logout-register-menu-security',
		);
	}
}
