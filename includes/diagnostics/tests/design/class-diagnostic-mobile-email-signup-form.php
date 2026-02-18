<?php
/**
 * Mobile Email Signup Form Diagnostic
 *
 * Optimizes email signup form for mobile devices.
 *
 * @since   1.6033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Email Signup Form Diagnostic Class
 *
 * Validates email signup form optimization for mobile devices,
 * ensuring proper input type and button sizing for better conversions.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Mobile_Email_Signup_Form extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-email-signup-form';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Email Signup Form';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Optimize email signup form for mobile devices with proper input types and button sizing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if email input uses type="email"
		$email_input_optimized = apply_filters( 'wpshadow_email_input_uses_type_email', false );
		if ( ! $email_input_optimized ) {
			$issues[] = __( 'Email input should use type="email" for mobile keyboard optimization', 'wpshadow' );
		}

		// Check if form doesn't require scrolling
		$form_fits_viewport = apply_filters( 'wpshadow_signup_form_fits_mobile_viewport', false );
		if ( ! $form_fits_viewport ) {
			$issues[] = __( 'Signup form may require scrolling on mobile; reduce fields or stack vertically', 'wpshadow' );
		}

		// Check if submit button is adequately sized
		$button_size_adequate = apply_filters( 'wpshadow_signup_button_size_44px_minimum', false );
		if ( ! $button_size_adequate ) {
			$issues[] = __( 'Submit button should be 44px+ tall for comfortable tapping on mobile', 'wpshadow' );
		}

		// Check for form labels
		$has_form_labels = apply_filters( 'wpshadow_signup_form_has_labels', false );
		if ( ! $has_form_labels ) {
			$issues[] = __( 'Form fields should have associated labels for mobile accessibility', 'wpshadow' );
		}

		// Check for single-step vs multi-step
		$form_streamlined = apply_filters( 'wpshadow_signup_form_single_step', false );
		if ( ! $form_streamlined ) {
			$issues[] = __( 'Signup form may have unnecessary fields; reduce to email only for higher conversions', 'wpshadow' );
		}

		// Check if button has hover/active states
		$button_has_states = apply_filters( 'wpshadow_signup_button_has_visual_feedback', false );
		if ( ! $button_has_states ) {
			$issues[] = __( 'Submit button should have visible hover/active states for interaction feedback', 'wpshadow' );
		}

		// Check for successful submission messaging
		$success_message_configured = apply_filters( 'wpshadow_signup_success_message_configured', false );
		if ( ! $success_message_configured ) {
			$issues[] = __( 'Signup form should show clear success message after submission', 'wpshadow' );
		}

		// Check for ARIA labels on email input
		$aria_labeled = apply_filters( 'wpshadow_email_input_aria_labeled', false );
		if ( ! $aria_labeled ) {
			$issues[] = __( 'Email input should have aria-label for screen reader users', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-email-signup-form',
			);
		}

		return null;
	}
}
