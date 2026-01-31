<?php
/**
 * Login Logout Register Menu Redirect Diagnostic
 *
 * Login Logout Register Menu Redirect issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1231.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Logout Register Menu Redirect Diagnostic Class
 *
 * @since 1.1231.0000
 */
class Diagnostic_LoginLogoutRegisterMenuRedirect extends Diagnostic_Base {

	protected static $slug = 'login-logout-register-menu-redirect';
	protected static $title = 'Login Logout Register Menu Redirect';
	protected static $description = 'Login Logout Register Menu Redirect issue found';
	protected static $family = 'functionality';

	public static function check() {
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/login-logout-register-menu-redirect',
			);
		}
		
		return null;
	}
}
