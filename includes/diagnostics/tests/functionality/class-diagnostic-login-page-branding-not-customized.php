<?php
/**
 * Login Page Branding Not Customized Diagnostic
 *
 * Checks if login page is branded.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2325
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Page Branding Not Customized Diagnostic Class
 *
 * Detects generic login page.
 *
 * @since 1.2601.2325
 */
class Diagnostic_Login_Page_Branding_Not_Customized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-branding-not-customized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Branding Not Customized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if login page is branded';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2325
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for custom login page plugins
		$login_plugins = array(
			'customize-login-page/customize-login-page.php',
			'white-label-branding/white-label-branding.php',
		);

		$login_custom = false;
		foreach ( $login_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$login_custom = true;
				break;
			}
		}

		if ( ! $login_custom ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Login page is not branded. Customize it to match your site branding for a professional appearance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/login-page-branding-not-customized',
			);
		}

		return null;
	}
}
