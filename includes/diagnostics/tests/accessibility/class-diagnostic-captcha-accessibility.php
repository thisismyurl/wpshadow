<?php
/**
 * CAPTCHA Without Alternative Diagnostic
 *
 * Issue #4760: CAPTCHA Without Accessible Alternative
 * Pillar: 🌍 Accessibility First
 *
 * Checks if CAPTCHA systems have accessible alternatives.
 * Image-based CAPTCHAs block blind users from accessing forms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6036.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CAPTCHA_Accessibility Class
 *
 * Checks for accessible CAPTCHA implementations.
 *
 * @since 1.6036.1445
 */
class Diagnostic_CAPTCHA_Accessibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'captcha-accessibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CAPTCHA Without Accessible Alternative';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CAPTCHA systems provide accessible alternatives for blind users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6036.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for common CAPTCHA plugins.
		$captcha_plugins = array(
			'really-simple-captcha/really-simple-captcha.php'          => 'Really Simple CAPTCHA',
			'captcha/captcha.php'                                      => 'Google Captcha (reCAPTCHA)',
			'advanced-nocaptcha-recaptcha/advanced-nocaptcha-recaptcha.php' => 'Advanced noCAPTCHA reCAPTCHA',
			'invisible-recaptcha/invisible-recaptcha.php'              => 'Invisible reCAPTCHA',
		);

		$active_captcha = array();
		foreach ( $captcha_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_captcha[] = $plugin_name;
			}
		}

		if ( ! empty( $active_captcha ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of CAPTCHA plugin names */
				__( 'Active CAPTCHA plugins detected: %s', 'wpshadow' ),
				implode( ', ', $active_captcha )
			);
		}

		$issues[] = __( 'Use reCAPTCHA v3 (invisible, no user interaction)', 'wpshadow' );
		$issues[] = __( 'Provide audio CAPTCHA alternative for blind users', 'wpshadow' );
		$issues[] = __( 'Use hCaptcha with accessibility features enabled', 'wpshadow' );
		$issues[] = __( 'Better: use honeypot fields (no CAPTCHA at all)', 'wpshadow' );
		$issues[] = __( 'Best: use behavioral analysis (detect human patterns)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your forms might use CAPTCHA challenges that blind users cannot solve. Traditional CAPTCHAs show distorted text in an image and ask users to type what they see—this is impossible for screen reader users. While some CAPTCHAs offer audio alternatives, these are often difficult to understand even for hearing users. The impact: blind users (2% of population) are completely blocked from registering, contacting you, or making purchases. Modern solutions like reCAPTCHA v3 or honeypot fields stop bots without blocking real users. For ADA compliance, always provide an accessible alternative or, better yet, don\'t use visual challenges at all.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/captcha-accessibility',
				'details'      => array(
					'recommendations'        => $issues,
					'wcag_requirement'       => 'WCAG 2.1 1.1.1 Non-text Content (Level A)',
					'affected_users'         => 'Blind users (2%), cognitive disabilities, dyslexia',
					'legal_risk'             => 'ADA lawsuits for inaccessible CAPTCHAs have succeeded',
					'accessible_options'     => 'reCAPTCHA v3 (invisible), hCaptcha (SR mode), honeypot',
					'best_practice'          => 'Avoid CAPTCHAs entirely—use behavioral analysis instead',
					'active_captcha_plugins' => $active_captcha,
				),
			);
		}

		return null;
	}
}
