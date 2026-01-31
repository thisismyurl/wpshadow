<?php
/**
 * Data Minimization Compliance Diagnostic
 *
 * Ensures only necessary data is collected per GDPR Article 5(1)(c).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.6028.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Minimization Diagnostic Class
 *
 * @since 1.6028.1500
 */
class Diagnostic_DataMinimizationCompliance extends Diagnostic_Base {

	protected static $slug        = 'data-minimization-compliance';
	protected static $title       = 'Data Minimization Compliance';
	protected static $description = 'Ensure only necessary data is collected';
	protected static $family      = 'privacy-gdpr';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check contact forms for excessive fields.
		$forms_with_issues = array();
		
		// Check if Contact Form 7 is active.
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) && function_exists( 'wpcf7_contact_form' ) ) {
			$cf7_forms = get_posts(
				array(
					'post_type'      => 'wpcf7_contact_form',
					'posts_per_page' => 10,
				)
			);

			foreach ( $cf7_forms as $form ) {
				$content = $form->post_content;
				// Count fields.
				$field_count = preg_match_all( '/\[.*?type=/', $content, $matches );
				
				if ( $field_count > 7 ) {
					$forms_with_issues[] = array(
						'title'       => $form->post_title,
						'field_count' => $field_count,
					);
				}
			}
		}

		// Check user registration fields.
		$registration_fields = array();
		if ( get_option( 'users_can_register' ) ) {
			// Count custom registration fields (basic check).
			$show_admin_bar_default = get_option( 'show_avatars' );
			if ( has_filter( 'register_form' ) ) {
				$registration_fields[] = 'custom_fields_detected';
			}
		}

		if ( ! empty( $forms_with_issues ) || ! empty( $registration_fields ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of forms with issues */
					__( 'Found %d forms collecting potentially excessive data', 'wpshadow' ),
					count( $forms_with_issues )
				),
				'severity'     => 'medium',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/data-minimization',
				'meta'         => array(
					'forms_with_issues' => $forms_with_issues,
					'registration_check' => ! empty( $registration_fields ),
				),
				'details'      => array(
					'finding'        => __( 'Forms may be collecting more data than necessary', 'wpshadow' ),
					'impact'         => __( 'GDPR Article 5(1)(c) requires data minimization. Storing unused data = violation + liability.', 'wpshadow' ),
					'recommendation' => __( 'Review forms and keep only essential fields', 'wpshadow' ),
					'solution_free'  => array(
						'label' => __( 'Form Field Audit', 'wpshadow' ),
						'steps' => array(
							__( '1. Review each form field for necessity', 'wpshadow' ),
							__( '2. Remove fields not directly needed', 'wpshadow' ),
							__( '3. Make optional fields clearly optional', 'wpshadow' ),
							__( '4. Document justification for required fields', 'wpshadow' ),
						),
					),
				),
			);
		}

		return null;
	}
}
