<?php
/**
 * Form Accessibility Testing Treatment
 *
 * Tests if forms are accessible with proper labels and error messages.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1340
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Accessibility Testing Treatment Class
 *
 * Validates that forms have proper labels, error messages, and
 * keyboard accessibility for users with disabilities.
 *
 * @since 1.7034.1340
 */
class Treatment_Form_Accessibility_Testing extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-accessibility-testing';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Form Accessibility Testing';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if forms are accessible with proper labels and error messages';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * Tests form accessibility including label associations, required
	 * field indicators, error messages, and fieldset grouping.
	 *
	 * @since  1.7034.1340
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for form plugins.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
			'gravityforms/gravityforms.php'        => 'Gravity Forms',
			'wpforms-lite/wpforms.php'             => 'WPForms',
			'formidable/formidable.php'            => 'Formidable Forms',
		);

		$active_form_plugins = array();
		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_form_plugins[] = $name;
			}
		}

		$has_form_plugin = ! empty( $active_form_plugins );

		// Check Contact Form 7 forms for accessibility.
		$cf7_forms_accessible = true;
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			global $wpdb;
			$cf7_forms = $wpdb->get_results(
				"SELECT post_content FROM {$wpdb->posts}
				 WHERE post_type = 'wpcf7_contact_form'
				 LIMIT 5",
				ARRAY_A
			);

			foreach ( $cf7_forms as $form ) {
				$content = $form['post_content'];
				// Check for proper label structure.
				if ( strpos( $content, '<label' ) === false ) {
					$cf7_forms_accessible = false;
					break;
				}
			}
		}

		// Check comment form for accessibility.
		$comment_form_file = get_template_directory() . '/comments.php';
		$comment_form_accessible = false;

		if ( file_exists( $comment_form_file ) ) {
			$comment_content = file_get_contents( $comment_form_file );
			$comment_form_accessible = ( strpos( $comment_content, '<label' ) !== false ) &&
									 ( strpos( $comment_content, 'for=' ) !== false );
		}

		// Check search form.
		$searchform_file = get_template_directory() . '/searchform.php';
		$search_form_accessible = false;

		if ( file_exists( $searchform_file ) ) {
			$search_content = file_get_contents( $searchform_file );
			$search_form_accessible = ( strpos( $search_content, '<label' ) !== false ) ||
									( strpos( $search_content, 'aria-label' ) !== false );
		}

		// Check for required field indicators.
		$has_required_indicators = false;
		if ( file_exists( $searchform_file ) ) {
			$search_content = file_get_contents( $searchform_file );
			$has_required_indicators = ( strpos( $search_content, 'required' ) !== false ) ||
									 ( strpos( $search_content, 'aria-required' ) !== false );
		}

		// Check for error message styling.
		$style_css = get_stylesheet_directory() . '/style.css';
		$has_error_styles = false;

		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			$has_error_styles = ( strpos( $style_content, '.error' ) !== false ) ||
							  ( strpos( $style_content, 'aria-invalid' ) !== false );
		}

		// Check for fieldset grouping.
		$uses_fieldsets = false;
		if ( $has_form_plugin && file_exists( $comment_form_file ) ) {
			$comment_content = file_get_contents( $comment_form_file );
			$uses_fieldsets = ( strpos( $comment_content, '<fieldset' ) !== false );
		}

		// Check autocomplete attributes.
		$uses_autocomplete = false;
		if ( file_exists( $comment_form_file ) ) {
			$comment_content = file_get_contents( $comment_form_file );
			$uses_autocomplete = ( strpos( $comment_content, 'autocomplete=' ) !== false );
		}

		// Check for issues.
		$issues = array();

		// Issue 1: No form plugin installed.
		if ( ! $has_form_plugin ) {
			$issues[] = array(
				'type'        => 'no_form_plugin',
				'description' => __( 'No form plugin detected; custom forms may lack accessibility features', 'wpshadow' ),
			);
		}

		// Issue 2: CF7 forms not accessible.
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) && ! $cf7_forms_accessible ) {
			$issues[] = array(
				'type'        => 'cf7_not_accessible',
				'description' => __( 'Contact Form 7 forms lack proper label tags; screen readers cannot identify fields', 'wpshadow' ),
			);
		}

		// Issue 3: Comment form not accessible.
		if ( ! $comment_form_accessible ) {
			$issues[] = array(
				'type'        => 'comment_form_not_accessible',
				'description' => __( 'Comment form lacks proper labels; form fields not identified for screen readers', 'wpshadow' ),
			);
		}

		// Issue 4: Search form not accessible.
		if ( ! $search_form_accessible ) {
			$issues[] = array(
				'type'        => 'search_form_not_accessible',
				'description' => __( 'Search form lacks label or ARIA label; screen readers cannot identify search input', 'wpshadow' ),
			);
		}

		// Issue 5: No error message styling.
		if ( ! $has_error_styles ) {
			$issues[] = array(
				'type'        => 'no_error_styles',
				'description' => __( 'No error message styling; users cannot visually identify form validation errors', 'wpshadow' ),
			);
		}

		// Issue 6: No fieldset grouping for related fields.
		if ( $has_form_plugin && ! $uses_fieldsets ) {
			$issues[] = array(
				'type'        => 'no_fieldsets',
				'description' => __( 'Forms do not use fieldsets; related fields not grouped for screen readers', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Forms are not fully accessible, preventing users with disabilities from submitting information effectively', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/form-accessibility-testing',
				'details'      => array(
					'has_form_plugin'         => $has_form_plugin,
					'active_form_plugins'     => $active_form_plugins,
					'cf7_forms_accessible'    => $cf7_forms_accessible,
					'comment_form_accessible' => $comment_form_accessible,
					'search_form_accessible'  => $search_form_accessible,
					'has_required_indicators' => $has_required_indicators,
					'has_error_styles'        => $has_error_styles,
					'uses_fieldsets'          => $uses_fieldsets,
					'uses_autocomplete'       => $uses_autocomplete,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Add labels to all inputs, use aria-required, style errors, implement fieldset grouping', 'wpshadow' ),
					'wcag_requirements'       => array(
						'WCAG 1.3.1' => 'Info and Relationships - Associate labels with inputs',
						'WCAG 3.3.2' => 'Labels or Instructions - Provide clear labels',
						'WCAG 3.3.1' => 'Error Identification - Clearly identify errors',
						'WCAG 3.3.3' => 'Error Suggestion - Suggest how to fix errors',
						'WCAG 4.1.3' => 'Status Messages - Announce validation errors',
					),
					'accessible_form_example' => array(
						'Label association' => '<label for="email">Email:</label><input id="email" type="email">',
						'Required field'    => '<input required aria-required="true">',
						'Error message'     => '<span role="alert">Email is required</span>',
						'Fieldset'          => '<fieldset><legend>Contact Info</legend>...</fieldset>',
						'Autocomplete'      => '<input autocomplete="email">',
					),
					'form_validation'         => 'Error messages must be programmatically associated with invalid fields',
					'keyboard_navigation'     => 'All form controls must be reachable via Tab key',
				),
			);
		}

		return null;
	}
}
