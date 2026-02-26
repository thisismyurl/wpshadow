<?php
/**
 * Mobile Form Validation Feedback Treatment
 *
 * Validates that form validation errors are clearly visible and accessible
 * on mobile devices with appropriate ARIA attributes.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1210
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Form Validation Feedback Treatment Class
 *
 * Checks that forms provide clear, accessible validation feedback on mobile.
 * Validates ARIA attributes, error message visibility, and inline error patterns.
 *
 * WCAG Reference: 3.3.1 Error Identification (Level A), 3.3.3 Error Suggestion (Level AA)
 *
 * @since 1.602.1210
 */
class Treatment_Mobile_Form_Validation_Feedback extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-form-validation-feedback';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Form Validation Feedback';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that form validation errors are clearly visible and accessible on mobile with ARIA attributes';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1210
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Form_Validation_Feedback' );
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
