<?php
/**
 * Form Error Association Diagnostic
 *
 * Checks if error messages use aria-describedby to link to form fields.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Error Association Diagnostic Class
 *
 * Validates that form errors are programmatically linked to fields.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Form_Error_Association extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-error-association';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Error Messages Not Associated with Fields';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if error messages use aria-describedby to link to fields';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check theme templates for forms.
		$form_templates = array(
			get_template_directory() . '/comments.php',
			get_template_directory() . '/contact.php',
			get_template_directory() . '/template-parts/contact-form.php',
		);

		$forms_found          = 0;
		$forms_without_aria   = 0;

		foreach ( $form_templates as $template ) {
			if ( ! file_exists( $template ) ) {
				continue;
			}

			$content = file_get_contents( $template );

			// Check if template has forms.
			if ( ! preg_match( '/<form[^>]*>/i', $content ) ) {
				continue;
			}

			$forms_found++;

			// Check for aria-describedby on inputs.
			if ( ! preg_match( '/aria-describedby=/i', $content ) ) {
				$forms_without_aria++;
			}

			// Check for aria-invalid.
			if ( ! preg_match( '/aria-invalid=/i', $content ) ) {
				$issues[] = sprintf(
					/* translators: %s: template file basename */
					__( 'Form in %s does not use aria-invalid for error states', 'wpshadow' ),
					basename( $template )
				);
			}

			// Check for role="alert" on error messages.
			if ( preg_match( '/error|validation/i', $content ) && ! preg_match( '/role=["\']alert["\']/', $content ) ) {
				$issues[] = sprintf(
					/* translators: %s: template file basename */
					__( 'Form in %s does not use role="alert" for error announcements', 'wpshadow' ),
					basename( $template )
				);
			}
		}

		if ( $forms_without_aria > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of forms without aria-describedby */
				__( 'Found %d forms without aria-describedby linking errors to fields', 'wpshadow' ),
				$forms_without_aria
			);
		}

		// Check active form plugins.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
			'wpforms-lite/wpforms.php'             => 'WPForms',
			'gravityforms/gravityforms.php'        => 'Gravity Forms',
		);

		$has_form_plugin = false;
		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_form_plugin = true;
				break;
			}
		}

		if ( ! $has_form_plugin && $forms_found === 0 ) {
			$issues[] = __( 'No accessible form plugin detected. Consider WPForms or Gravity Forms for better accessibility', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your form errors aren\'t linked to the fields that caused them—like getting a test back with just a grade and no marks showing what you missed. Screen reader users hear "Error: Email is invalid" but don\'t know which of the 10 form fields is the email field. The aria-describedby attribute programmatically links error messages to their fields, so assistive technology can announce "Email field, Error: Please enter a valid email address."', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/form-error-association',
			);
		}

		return null;
	}
}
