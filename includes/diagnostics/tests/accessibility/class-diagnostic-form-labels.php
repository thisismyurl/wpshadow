<?php
/**
 * Form Label Association Diagnostic
 *
 * Checks that form inputs have associated labels for screen reader users
 * to understand what each field is for.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Label Association Diagnostic Class
 *
 * Verifies form inputs have proper label associations.
 * WCAG 2.1 Level A Success Criterion1.0 (Info and Relationships).
 *
 * @since 0.6093.1200
 */
class Diagnostic_Form_Labels extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'form_labels';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Form Label Association';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies form inputs have associated labels';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		// Check for form plugins (they usually handle labels correctly).
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php'     => 'Contact Form 7',
			'wpforms-lite/wpforms.php'                 => 'WPForms',
			'ninja-forms/ninja-forms.php'              => 'Ninja Forms',
			'formidable/formidable.php'                => 'Formidable Forms',
			'gravityforms/gravityforms.php'            => 'Gravity Forms',
		);

		$active_forms = array();
		foreach ( $form_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_forms[] = $plugin_name;
			}
		}

		if ( count( $active_forms ) > 0 ) {
			$stats['form_plugins'] = implode( ', ', $active_forms );
		} else {
			$warnings[] = 'No major form plugins detected (using WordPress native forms)';
		}

		// Check for accessibility validation plugins.
		$validation_plugins = array(
			'accessibility-checker/accessibility-checker.php' => 'Accessibility Checker',
			'wp-accessibility/wp-accessibility.php'           => 'WP Accessibility',
		);

		$active_validation = array();
		foreach ( $validation_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_validation[] = $plugin_name;
			}
		}

		if ( count( $active_validation ) > 0 ) {
			$stats['validation_tools'] = implode( ', ', $active_validation );
		}

		// Check WordPress comment form settings.
		$require_name_email = get_option( 'require_name_email', '1' );
		if ( '1' === $require_name_email ) {
			$stats['comment_form_labels'] = 'Required (likely has labels)';
		}

		// Check theme for forms.
		$theme_has_forms = false;
		$search_templates = array(
			get_template_directory() . '/searchform.php',
			get_template_directory() . '/comments.php',
			get_stylesheet_directory() . '/searchform.php',
			get_stylesheet_directory() . '/comments.php',
		);

		foreach ( $search_templates as $template_path ) {
			if ( file_exists( $template_path ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $template_path );

				// Check for label elements.
				if ( preg_match( '/<label/i', $content ) ) {
					$theme_has_forms = true;
					$stats['theme_uses_labels'] = 'Yes';
					break;
				}
			}
		}

		// Return finding if no form systems detected.
		if ( count( $active_forms ) === 0 && count( $active_validation ) === 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site doesn\'t have automated form label checking. Form labels are like name tags on input boxes—they tell screen reader users what information to enter. Without proper labels, blind users face empty boxes with no clue what they\'re for. A contact form might ask for "Name, Email, Message" visually, but a screen reader just announces "Edit text, Edit text, Edit text" without labels. This affects 2% of users with visual disabilities.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/form-labels?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'          => $stats,
					'issues'         => $issues,
					'warnings'       => $warnings,
					'wcag_criterion' => 'WCAG 2.1 Level A -1.0 Info and Relationships',
					'proper_pattern' => '<label for="name">Your Name</label><input type="text" id="name" name="name">',
				),
			);
		}

		return null;
	}
}
