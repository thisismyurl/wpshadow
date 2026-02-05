<?php
/**
 * Contact Form 7 Form Field Validation and Security Treatment
 *
 * Checks Contact Form 7 forms for proper validation, sanitization, and security measures.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Form 7 Validation Treatment Class
 *
 * Verifies CF7 forms have proper validation and security measures in place.
 *
 * @since 1.6031.1200
 */
class Treatment_Contact_Form_7_Validation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'contact-form-7-validation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Contact Form 7 Form Field Validation and Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures CF7 forms have proper validation, sanitization, and security measures';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6031.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Contact Form 7 is active.
		if ( ! class_exists( 'WPCF7' ) ) {
			return null; // Plugin not active, no check needed.
		}

		$issues = array();

		// Get all CF7 forms.
		$forms = get_posts(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		if ( empty( $forms ) ) {
			return null; // No forms to check.
		}

		foreach ( $forms as $form ) {
			$form_obj  = \WPCF7_ContactForm::get_instance( $form->ID );
			$form_html = $form_obj->prop( 'form' );

			$form_issues = self::analyze_form_security( $form->ID, $form_html, $form->post_title );
			if ( ! empty( $form_issues ) ) {
				$issues = array_merge( $issues, $form_issues );
			}
		}

		if ( empty( $issues ) ) {
			return null; // No issues found.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of security issues found */
				__( 'Found %d security or validation issues in Contact Form 7 forms', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/contact-form-7-validation',
		);
	}

	/**
	 * Analyze form for security issues.
	 *
	 * @since  1.6031.1200
	 * @param  int    $form_id   Form ID.
	 * @param  string $form_html Form HTML content.
	 * @param  string $form_name Form name/title.
	 * @return array Array of issues found.
	 */
	private static function analyze_form_security( $form_id, $form_html, $form_name ) {
		$issues = array();

		// Check for email validation.
		if ( preg_match( '/\[email\s+(?!.*email\*)/', $form_html ) ) {
			$issues[] = array(
				'form_id'     => $form_id,
				'form_name'   => $form_name,
				'issue_type'  => 'weak_email_validation',
				'description' => __( 'Email field lacks proper validation (should use email* for required)', 'wpshadow' ),
				'severity'    => 'medium',
			);
		}

		// Check for phone validation.
		if ( preg_match( '/\[tel\s+/', $form_html ) && ! preg_match( '/\[tel\s+[^\]]*tel-\d{3}/', $form_html ) ) {
			$issues[] = array(
				'form_id'     => $form_id,
				'form_name'   => $form_name,
				'issue_type'  => 'no_phone_validation',
				'description' => __( 'Phone field lacks format validation', 'wpshadow' ),
				'severity'    => 'low',
			);
		}

		// Check for file upload restrictions.
		if ( preg_match( '/\[file\s+/', $form_html ) ) {
			// Check if limit attribute is present.
			if ( ! preg_match( '/\[file\s+[^\]]*limit:/', $form_html ) ) {
				$issues[] = array(
					'form_id'     => $form_id,
					'form_name'   => $form_name,
					'issue_type'  => 'file_upload_no_size_limit',
					'description' => __( 'File upload field has no size limit restriction', 'wpshadow' ),
					'severity'    => 'high',
				);
			}

			// Check if filetypes attribute is present.
			if ( ! preg_match( '/\[file\s+[^\]]*filetypes:/', $form_html ) ) {
				$issues[] = array(
					'form_id'     => $form_id,
					'form_name'   => $form_name,
					'issue_type'  => 'file_upload_no_type_restriction',
					'description' => __( 'File upload field has no file type restrictions', 'wpshadow' ),
					'severity'    => 'high',
				);
			}
		}

		// Check for textarea with no maxlength.
		if ( preg_match_all( '/\[textarea\s+([^\]]+)\]/', $form_html, $matches ) ) {
			foreach ( $matches[1] as $textarea_attrs ) {
				if ( ! preg_match( '/maxlength:/', $textarea_attrs ) ) {
					$issues[] = array(
						'form_id'     => $form_id,
						'form_name'   => $form_name,
						'issue_type'  => 'textarea_no_maxlength',
						'description' => __( 'Textarea field has no maximum length restriction', 'wpshadow' ),
						'severity'    => 'medium',
					);
					break; // Only report once per form.
				}
			}
		}

		// Check for required field validation.
		$required_count = preg_match_all( '/\[[a-z]+\*\s+/', $form_html );
		if ( $required_count === 0 ) {
			$issues[] = array(
				'form_id'     => $form_id,
				'form_name'   => $form_name,
				'issue_type'  => 'no_required_fields',
				'description' => __( 'Form has no required fields, may lead to spam submissions', 'wpshadow' ),
				'severity'    => 'medium',
			);
		}

		// Check for spam protection (Akismet, reCAPTCHA, etc.).
		$has_spam_protection = false;
		if ( preg_match( '/\[recaptcha\]|\[really-simple-captcha\]/', $form_html ) ) {
			$has_spam_protection = true;
		}

		// Check if Akismet is active.
		if ( ! $has_spam_protection && ! class_exists( 'Akismet' ) ) {
			$issues[] = array(
				'form_id'     => $form_id,
				'form_name'   => $form_name,
				'issue_type'  => 'no_spam_protection',
				'description' => __( 'Form has no spam protection (no CAPTCHA or Akismet)', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		// Check for acceptance checkbox (for GDPR compliance).
		if ( ! preg_match( '/\[acceptance\s+/', $form_html ) ) {
			$issues[] = array(
				'form_id'     => $form_id,
				'form_name'   => $form_name,
				'issue_type'  => 'no_acceptance_checkbox',
				'description' => __( 'Form lacks acceptance/consent checkbox (recommended for GDPR)', 'wpshadow' ),
				'severity'    => 'low',
			);
		}

		return $issues;
	}
}
