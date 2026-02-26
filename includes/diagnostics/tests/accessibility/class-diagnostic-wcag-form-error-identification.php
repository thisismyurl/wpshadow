<?php
/**
 * WCAG 3.3.1 Error Identification Diagnostic
 *
 * Validates that form errors are clearly identified and associated with fields.
 *
 * @since   1.6035.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Form Error Identification Diagnostic Class
 *
 * Checks for proper form error identification (WCAG 3.3.1 Level A).
 *
 * @since 1.6035.1200
 */
class Diagnostic_WCAG_Form_Error_Identification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-form-error-identification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Error Identification (WCAG 3.3.1)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that form errors are clearly identified';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for accessible form plugins.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
			'wpforms-lite/wpforms.php'             => 'WPForms',
			'gravityforms/gravityforms.php'        => 'Gravity Forms',
			'ninja-forms/ninja-forms.php'          => 'Ninja Forms',
			'formidable/formidable.php'            => 'Formidable Forms',
			'wpforms/wpforms.php'                  => 'WPForms Pro',
		);

		$active_plugins      = get_option( 'active_plugins', array() );
		$has_form_plugin     = false;
		$active_form_plugins = array();

		foreach ( $form_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_form_plugin       = true;
				$active_form_plugins[] = $name;
			}
		}

		if ( ! $has_form_plugin ) {
			// Check for theme forms.
			$theme_templates = array(
				'/comments.php',
				'/contact.php',
				'/template-parts/contact-form.php',
				'/inc/contact-form.php',
			);

			$has_theme_form = false;
			foreach ( $theme_templates as $template ) {
				$file = get_template_directory() . $template;
				if ( file_exists( $file ) ) {
					$content = file_get_contents( $file );

					// Check if it contains form elements.
					if ( preg_match( '/<form[^>]*>/', $content ) ) {
						$has_theme_form = true;

						// Check for ARIA error handling.
						if ( ! preg_match( '/aria-invalid|aria-describedby|aria-errormessage/', $content ) ) {
							$issues[] = __( 'Theme form does not use ARIA attributes (aria-invalid, aria-describedby) for error identification', 'wpshadow' );
						}

						// Check for required field indicators.
						if ( ! preg_match( '/required|aria-required/', $content ) ) {
							$issues[] = __( 'Theme form does not clearly mark required fields with required or aria-required attributes', 'wpshadow' );
						}

						break;
					}
				}
			}

			if ( ! $has_theme_form ) {
				$issues[] = __( 'No form plugin detected and no theme forms found. If you have contact forms, use an accessible plugin', 'wpshadow' );
			}
		}

		// Check WordPress core comment form.
		if ( ! current_theme_supports( 'html5', array( 'comment-form' ) ) ) {
			$issues[] = __( 'Theme should add HTML5 support for comment forms: add_theme_support(\'html5\', array(\'comment-form\'))', 'wpshadow' );
		}

		// Check if comment form has custom styling that might hide errors.
		$theme_css = get_template_directory() . '/style.css';
		if ( file_exists( $theme_css ) ) {
			$content = file_get_contents( $theme_css );

			// Check if error styles are hidden.
			if ( preg_match( '/\.(?:error|invalid|validation-error)[^{]*{[^}]*display\s*:\s*none/i', $content ) ) {
				$issues[] = __( 'Theme CSS hides error messages with display:none. Errors must be visible to users', 'wpshadow' );
			}
		}

		// Check for JavaScript validation without accessible fallback.
		$js_files = array();
		$theme_js = get_template_directory() . '/js';

		if ( is_dir( $theme_js ) ) {
			$files = glob( $theme_js . '/*.js' );
			if ( is_array( $files ) ) {
				$js_files = array_merge( $js_files, $files );
			}
		}

		$common_js_locations = array(
			get_template_directory() . '/assets/js',
			get_template_directory() . '/dist/js',
		);

		foreach ( $common_js_locations as $location ) {
			if ( is_dir( $location ) ) {
				$files = glob( $location . '/*.js' );
				if ( is_array( $files ) ) {
					$js_files = array_merge( $js_files, $files );
				}
			}
		}

		$has_js_validation = false;
		$has_aria_support  = false;

		foreach ( $js_files as $js_file ) {
			if ( ! file_exists( $js_file ) ) {
				continue;
			}

			$content = file_get_contents( $js_file );

			// Check for validation logic.
			if ( preg_match( '/\.validate\(|validation|checkValidity/', $content ) ) {
				$has_js_validation = true;

				// Check if it sets ARIA attributes.
				if ( preg_match( '/aria-invalid|aria-describedby|setAttribute.*aria/', $content ) ) {
					$has_aria_support = true;
				}
			}
		}

		if ( $has_js_validation && ! $has_aria_support ) {
			$issues[] = __( 'JavaScript form validation detected but does not set ARIA attributes for screen reader compatibility', 'wpshadow' );
		}

		// Check WooCommerce if active.
		if ( class_exists( 'WooCommerce' ) ) {
			// WooCommerce has built-in accessible validation, but check if theme overrides it.
			$wc_form_file = get_template_directory() . '/woocommerce/checkout/form-checkout.php';
			if ( file_exists( $wc_form_file ) ) {
				$content = file_get_contents( $wc_form_file );

				if ( ! preg_match( '/woocommerce-invalid|aria-invalid/', $content ) ) {
					$issues[] = __( 'Theme overrides WooCommerce checkout form but removes validation error handling', 'wpshadow' );
				}
			}
		}

		// Recommend validation plugin if needed.
		if ( count( $issues ) > 2 ) {
			$issues[] = __( 'Consider using WPForms or Gravity Forms for better accessibility support in forms', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Form errors should be like helpful GPS directions: clear, specific, and easy to find. When a form submission fails, users need to know exactly what went wrong and how to fix it. Screen reader users especially need errors programmatically linked to fields (using ARIA attributes) so their assistive technology can announce "Email field: Error: Please enter a valid email address." Without this, they\'re left guessing which field caused the problem—like trying to fix a car when someone just says "something\'s broken" without telling you what.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wcag-form-error-identification',
			);
		}

		return null;
	}
}
