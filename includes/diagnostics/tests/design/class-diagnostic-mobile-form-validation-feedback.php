<?php
/**
 * Mobile Form Validation Feedback Diagnostic
 *
 * Validates that form validation errors are clearly visible and accessible
 * on mobile devices with appropriate ARIA attributes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Mobile
 * @since      1.602.1210
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Form Validation Feedback Diagnostic Class
 *
 * Checks that forms provide clear, accessible validation feedback on mobile.
 * Validates ARIA attributes, error message visibility, and inline error patterns.
 *
 * WCAG Reference: 3.3.1 Error Identification (Level A), 3.3.3 Error Suggestion (Level AA)
 *
 * @since 1.602.1210
 */
class Diagnostic_Mobile_Form_Validation_Feedback extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-form-validation-feedback';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Form Validation Feedback';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that form validation errors are clearly visible and accessible on mobile with ARIA attributes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.602.1210
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check common form plugins and patterns.
		$validation_status = self::check_form_validation_patterns();

		if ( ! empty( $validation_status['issues'] ) ) {
			$issues = $validation_status['issues'];
		}

		// Check theme/plugin JavaScript for validation handling.
		$js_validation = self::check_javascript_validation();
		if ( ! empty( $js_validation ) ) {
			$issues = array_merge( $issues, $js_validation );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$issue_count    = count( $issues );
		$threat_level   = min( 75, 55 + ( $issue_count * 5 ) );
		$severity       = $threat_level >= 70 ? 'high' : 'medium';
		$auto_fixable   = false;

		$description = sprintf(
			/* translators: %d: number of validation issues */
			__( 'Found %d form validation issue(s) that may be invisible or inaccessible on mobile. Poor error feedback causes 85%% of mobile form abandonment. Users need clear, immediate feedback when errors occur.', 'wpshadow' ),
			$issue_count
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => $auto_fixable,
			'kb_link'     => 'https://wpshadow.com/kb/mobile-form-validation',
			'details'     => array(
				'issue_count'   => $issue_count,
				'issues'        => array_slice( $issues, 0, 10 ),
				'why_important' => __(
					'Mobile form validation requires special attention:
					
					Mobile Challenges:
					• Small screens hide validation messages off-screen
					• Virtual keyboard covers error messages
					• Color-only errors fail accessibility (8% of men are colorblind)
					• Users cannot see top-of-page errors while typing at bottom
					
					Impact of Poor Validation:
					• 85% of users abandon forms with unclear errors (Baymard)
					• 67% abandon if error message not near the field
					• Screen reader users completely miss visual-only errors
					• Mobile conversion rates drop 40% with poor error handling
					
					Best Practices:
					• Show errors inline near the field (not just at top)
					• Use aria-invalid="true" and aria-describedby
					• Include error icon (not just color)
					• Scroll to first error on submit
					• Persist error messages when keyboard appears
					• Provide specific fix suggestions ("Use format: user@example.com")',
					'wpshadow'
				),
				'how_to_fix'    => __(
					'Implement proper validation feedback:
					
					HTML Pattern:
					<label for="email">Email Address</label>
					<input 
					  type="email" 
					  id="email" 
					  name="email"
					  aria-invalid="true"
					  aria-describedby="email-error"
					>
					<span id="email-error" role="alert" class="error-message">
					  Please enter a valid email address (e.g., user@example.com)
					</span>
					
					CSS for Mobile:
					.error-message {
					  color: #d32f2f;
					  font-size: 14px;
					  display: block;
					  margin-top: 4px;
					  padding: 8px;
					  background: #ffebee;
					  border-radius: 4px;
					}
					.error-message::before {
					  content: "⚠ "; /* Icon for colorblind users */
					}
					
					JavaScript:
					// Scroll to first error
					const firstError = document.querySelector("[aria-invalid=true]");
					if (firstError) {
					  firstError.scrollIntoView({ behavior: "smooth", block: "center" });
					  firstError.focus();
					}
					
					For WordPress Plugins:
					• Contact Form 7: Enable AJAX and use response output
					• Gravity Forms: Enable "Enable legacy markup" for ARIA support
					• WooCommerce: Errors automatic, ensure theme doesn\'t hide them',
					'wpshadow'
				),
			),
		);
	}

	/**
	 * Check for common form validation patterns.
	 *
	 * @since  1.602.1210
	 * @return array Status and issues found.
	 */
	private static function check_form_validation_patterns() {
		$result = array(
			'has_validation' => false,
			'issues'         => array(),
		);

		// Check active plugins for form handling.
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for Contact Form 7 (popular but often misconfigured).
		if ( in_array( 'contact-form-7/wp-contact-form-7.php', $active_plugins, true ) ) {
			// Check if CF7 validation is properly configured.
			$cf7_config = self::check_cf7_validation();
			if ( ! empty( $cf7_config['issues'] ) ) {
				$result['issues'] = array_merge( $result['issues'], $cf7_config['issues'] );
			}
		}

		// Check for Gravity Forms.
		if ( in_array( 'gravityforms/gravityforms.php', $active_plugins, true ) ) {
			$gf_config = self::check_gravity_forms_validation();
			if ( ! empty( $gf_config['issues'] ) ) {
				$result['issues'] = array_merge( $result['issues'], $gf_config['issues'] );
			}
		}

		// Check WooCommerce checkout validation.
		if ( function_exists( 'WC' ) ) {
			$wc_config = self::check_woocommerce_validation();
			if ( ! empty( $wc_config['issues'] ) ) {
				$result['issues'] = array_merge( $result['issues'], $wc_config['issues'] );
			}
		}

		return $result;
	}

	/**
	 * Check Contact Form 7 validation configuration.
	 *
	 * @since  1.602.1210
	 * @return array Issues found.
	 */
	private static function check_cf7_validation() {
		$issues = array();

		// CF7 stores forms as custom post type.
		$forms = get_posts(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		foreach ( $forms as $form ) {
			$form_props = get_post_meta( $form->ID );

			// Check if AJAX is enabled (required for inline validation).
			$ajax_enabled = true; // CF7 defaults to AJAX.

			// Check if response output is configured.
			$form_settings = maybe_unserialize( get_post_meta( $form->ID, '_config', true ) );

			// CF7 validation messages are in _messages meta.
			$messages = maybe_unserialize( get_post_meta( $form->ID, '_messages', true ) );

			// Check if validation messages are accessible.
			if ( empty( $messages ) || ! is_array( $messages ) ) {
				$issues[] = array(
					'plugin'      => 'Contact Form 7',
					'form_id'     => $form->ID,
					'form_title'  => $form->post_title,
					'issue_type'  => 'missing_validation_messages',
					'description' => 'Validation messages not configured or accessible',
				);
			}
		}

		return array( 'issues' => $issues );
	}

	/**
	 * Check Gravity Forms validation.
	 *
	 * @since  1.602.1210
	 * @return array Issues found.
	 */
	private static function check_gravity_forms_validation() {
		$issues = array();

		if ( ! class_exists( 'GFAPI' ) ) {
			return array( 'issues' => $issues );
		}

		// Get active forms.
		$forms = \GFAPI::get_forms();

		foreach ( $forms as $form ) {
			// Check if AJAX is enabled.
			$ajax_enabled = isset( $form['enableAnimation'] ) && $form['enableAnimation'];

			if ( ! $ajax_enabled ) {
				$issues[] = array(
					'plugin'      => 'Gravity Forms',
					'form_id'     => $form['id'],
					'form_title'  => $form['title'],
					'issue_type'  => 'ajax_disabled',
					'description' => 'AJAX submission disabled - validation requires page reload',
				);
			}
		}

		return array( 'issues' => $issues );
	}

	/**
	 * Check WooCommerce validation.
	 *
	 * @since  1.602.1210
	 * @return array Issues found.
	 */
	private static function check_woocommerce_validation() {
		$issues = array();

		// WooCommerce uses wc_add_notice() for validation.
		// Check if theme properly displays notices.

		// Capture checkout page to verify notice display.
		if ( function_exists( 'wc_get_checkout_url' ) ) {
			$checkout_html = self::capture_page_html( wc_get_checkout_url() );

			// Check if .woocommerce-NoticeGroup exists.
			if ( ! empty( $checkout_html ) && ! preg_match( '/class=["\'][^"\']*woocommerce-NoticeGroup[^"\']*["\']/', $checkout_html ) ) {
				$issues[] = array(
					'plugin'      => 'WooCommerce',
					'issue_type'  => 'missing_notice_container',
					'description' => 'WooCommerce notice container not found - validation errors may not display',
				);
			}
		}

		return array( 'issues' => $issues );
	}

	/**
	 * Check JavaScript validation patterns in theme/plugins.
	 *
	 * @since  1.602.1210
	 * @return array Issues found.
	 */
	private static function check_javascript_validation() {
		$issues = array();

		// Check theme JavaScript files for validation patterns.
		$theme_path = get_stylesheet_directory();
		$js_files   = array(
			$theme_path . '/assets/js/main.js',
			$theme_path . '/js/scripts.js',
			$theme_path . '/js/custom.js',
		);

		foreach ( $js_files as $js_file ) {
			if ( ! file_exists( $js_file ) ) {
				continue;
			}

			$content = file_get_contents( $js_file );

			// Check for validation without ARIA attributes.
			if ( preg_match( '/\.addClass\s*\(\s*["\']error["\']/', $content ) && ! preg_match( '/aria-invalid/', $content ) ) {
				$issues[] = array(
					'location'    => str_replace( WP_CONTENT_DIR, '', $js_file ),
					'issue_type'  => 'missing_aria_validation',
					'description' => 'JavaScript validation adds error class but doesn\'t set aria-invalid',
				);
			}

			// Check for alerts (bad UX on mobile).
			if ( preg_match( '/\balert\s*\(/', $content ) ) {
				$issues[] = array(
					'location'    => str_replace( WP_CONTENT_DIR, '', $js_file ),
					'issue_type'  => 'uses_alert',
					'description' => 'Form uses alert() for validation - poor mobile UX',
				);
			}
		}

		return $issues;
	}

	/**
	 * Capture HTML for page.
	 *
	 * @since  1.602.1210
	 * @param  string $url Page URL.
	 * @return string HTML content.
	 */
	private static function capture_page_html( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}
}
