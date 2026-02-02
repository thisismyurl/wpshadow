<?php
/**
 * Admin Redirect Security After Login
 *
 * Checks if user redirects after login are secure and don't expose sensitive URLs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0643
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Redirect Security After Login
 *
 * @since 1.26033.0643
 */
class Diagnostic_Admin_Redirect_Security_After_Login extends Diagnostic_Base {

	protected static $slug = 'admin-redirect-security-after-login';
	protected static $title = 'Admin Redirect Security After Login';
	protected static $description = 'Verifies login redirects are secure';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check if login_url filter is used
		$has_login_redirect = has_filter( 'login_url' );
		if ( ! $has_login_redirect ) {
			$issues[] = __( 'No login redirect filter detected - redirects may expose sensitive URLs', 'wpshadow' );
		}

		// Check if allowed_redirect_hosts filter is used
		$has_redirect_validation = has_filter( 'allowed_redirect_hosts' );
		if ( ! $has_redirect_validation ) {
			$issues[] = __( 'Redirect validation filter not found - open redirect vulnerability possible', 'wpshadow' );
		}

		// Check for common redirect parameters
		$redirect_param = isset( $_REQUEST['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['redirect_to'] ) ) : '';
		if ( ! empty( $redirect_param ) && ! wp_http_validate_url( $redirect_param ) ) {
			$issues[] = __( 'Invalid redirect parameter detected - potential open redirect attack', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-redirect-security-after-login',
			);
		}

		return null;
	}
}
