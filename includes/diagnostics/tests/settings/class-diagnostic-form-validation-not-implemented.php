<?php
/**
 * Form Validation Not Implemented Diagnostic
 *
 * Checks if form validation is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Validation Not Implemented Diagnostic Class
 *
 * Detects missing form validation.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Form_Validation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-validation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Validation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form validation is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for form plugins with validation.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php'           => 'Contact Form 7',
			'wpforms-lite/wpforms.php'                       => 'WPForms Lite',
			'wpforms/wpforms.php'                            => 'WPForms',
			'gravityforms/gravityforms.php'                  => 'Gravity Forms',
			'formidable/formidable.php'                      => 'Formidable Forms',
			'ninja-forms/ninja-forms.php'                    => 'Ninja Forms',
			'forminator/forminator.php'                      => 'Forminator',
			'weforms/weforms.php'                            => 'weForms',
		);

		$form_plugin_detected = false;
		$form_plugin_name     = '';

		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$form_plugin_detected = true;
				$form_plugin_name     = $name;
				break;
			}
		}

		// Check for native WordPress comment form.
		$comments_enabled = get_option( 'default_comment_status' ) === 'open';

		// Check for WooCommerce (has built-in validation).
		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );

		// Check for custom form validation filters.
		$has_custom_validation = has_filter( 'wp_insert_comment' ) ||
		                          has_filter( 'preprocess_comment' ) ||
		                          has_action( 'comment_post' );

		// If no form plugin and no custom validation.
		if ( ! $form_plugin_detected && ! $has_custom_validation && ! $has_woocommerce ) {
			// Only flag if comments are enabled (basic forms exist).
			if ( $comments_enabled ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'Form validation not implemented. Comments are enabled but no validation detected beyond WordPress defaults. Install Contact Form 7 or WPForms for forms with proper validation, or add custom validation to comment forms.', 'wpshadow' ),
					'severity'    => 'low',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/form-validation',
					'details'     => array(
						'form_plugin_detected' => false,
						'comments_enabled'     => true,
						'has_woocommerce'      => false,
						'recommendation'        => __( 'Install WPForms Lite (free, 5M+ installs) or Contact Form 7 (free, 5M+ installs) for forms with built-in validation. Both include spam protection and field validation.', 'wpshadow' ),
						'validation_types'     => array(
							'client_side' => 'JavaScript validation before submission',
							'server_side' => 'PHP validation for security',
							'field_types' => 'Email, phone, number, URL validation',
							'required_fields' => 'Ensure critical fields filled',
						),
						'benefits'             => array(
							'data_quality' => 'Valid, usable form submissions',
							'user_experience' => 'Clear error messages guide users',
							'spam_reduction' => 'Validation helps block spam',
						),
					),
				);
			}
		}

		// No issues - forms have validation.
		return null;
	}
}
