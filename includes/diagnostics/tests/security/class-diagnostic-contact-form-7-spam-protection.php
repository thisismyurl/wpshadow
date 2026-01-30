<?php
/**
 * Contact Form 7 Spam Protection Configuration Diagnostic
 *
 * Verify spam protection enabled on all Contact Form 7 forms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6030.1215
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Form 7 Spam Protection Diagnostic Class
 *
 * @since 1.6030.1215
 */
class Diagnostic_ContactForm7SpamProtection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'contact-form-7-spam-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Contact Form 7 Spam Protection Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify spam protection enabled on all Contact Form 7 forms';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1215
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Contact Form 7 is active
		if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Check for reCAPTCHA integration
		$recaptcha_options = get_option( 'wpcf7', array() );
		$has_recaptcha = false;

		if ( isset( $recaptcha_options['recaptcha'] ) ) {
			$recaptcha_config = $recaptcha_options['recaptcha'];
			$has_recaptcha = ! empty( $recaptcha_config['sitekey'] ) && ! empty( $recaptcha_config['secret'] );
		}

		if ( ! $has_recaptcha ) {
			$issues[] = 'reCAPTCHA not configured';
		}

		// Check 2: Verify CAPTCHA on all public forms
		$args = array(
			'post_type'      => 'wpcf7_contact_form',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$forms = get_posts( $args );
		$total_forms = count( $forms );
		$forms_with_captcha = 0;

		foreach ( $forms as $form ) {
			$form_content = $form->post_content;

			// Check for various CAPTCHA implementations
			if ( strpos( $form_content, '[recaptcha]' ) !== false ||
				 strpos( $form_content, '[captchac]' ) !== false ||
				 strpos( $form_content, '[captchar]' ) !== false ||
				 strpos( $form_content, '[quiz' ) !== false ) {
				$forms_with_captcha++;
			}
		}

		if ( $total_forms > 0 && $forms_with_captcha < $total_forms ) {
			$unprotected_count = $total_forms - $forms_with_captcha;
			$issues[] = sprintf( '%d forms without CAPTCHA protection', $unprotected_count );
		}

		// Check 3: Test for Akismet integration
		$akismet_active = class_exists( 'Akismet' ) || defined( 'AKISMET_VERSION' );
		$akismet_configured = $akismet_active && get_option( 'wordpress_api_key', false );

		if ( ! $akismet_configured ) {
			$issues[] = 'Akismet not configured for spam filtering';
		}

		// Check 4: Check for honeypot fields
		$has_flamingo = class_exists( 'Flamingo_Inbound_Message' );
		$honeypot_plugin = is_plugin_active( 'contact-form-7-honeypot/honeypot.php' );

		if ( ! $honeypot_plugin ) {
			$issues[] = 'honeypot plugin not installed';
		}

		// Check 5: Verify really simple CAPTCHA or alternative
		$simple_captcha = is_plugin_active( 'really-simple-captcha/really-simple-captcha.php' );
		$math_captcha = is_plugin_active( 'advanced-nocaptcha-recaptcha/advanced-nocaptcha-recaptcha.php' );

		if ( ! $has_recaptcha && ! $simple_captcha && ! $math_captcha && ! $honeypot_plugin ) {
			$issues[] = 'no spam protection plugins installed';
		}

		// Check 6: Test for quiz fields as spam prevention
		$forms_with_quiz = 0;
		foreach ( $forms as $form ) {
			if ( strpos( $form->post_content, '[quiz' ) !== false ) {
				$forms_with_quiz++;
			}
		}

		// If no other protection and no quizzes, flag it
		if ( ! $has_recaptcha && ! $akismet_configured && $forms_with_quiz === 0 ) {
			$issues[] = 'no quiz fields for basic spam prevention';
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 75 + ( count( $issues ) * 5 ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Contact Form 7 spam protection issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/contact-form-7-spam-protection',
			);
		}

		return null;
	}
}
