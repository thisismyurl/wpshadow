<?php declare(strict_types=1);
/**
 * Login Page CAPTCHA Diagnostic
 *
 * Philosophy: Bot prevention - require CAPTCHA on login
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if CAPTCHA is required on login.
 */
class Diagnostic_Login_Captcha {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$captcha_plugins = array(
			'google-captcha/google-captcha.php',
			'wp-recaptcha-integration/wp-recaptcha-integration.php',
			'invisible-recaptcha/invisible-recaptcha.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $captcha_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'login-captcha',
			'title'       => 'Login Page Missing CAPTCHA',
			'description' => 'No CAPTCHA protection on login form. Bots can brute force accounts without human verification. Add reCAPTCHA to login and registration forms.',
			'severity'    => 'medium',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/add-captcha-to-login/',
			'training_link' => 'https://wpshadow.com/training/bot-prevention/',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}
}
