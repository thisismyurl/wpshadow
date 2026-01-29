<?php
/**
 * Form Label Association Validation Diagnostic
 *
 * Ensures all form inputs have proper <label> associations for accessibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Label Association Validation Class
 *
 * Tests form label associations.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Form_Label_Association_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-label-association-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Label Association Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures all form inputs have proper <label> associations for accessibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$form_check = self::validate_form_labels();
		
		if ( $form_check['total_issues'] > 0 ) {
			$issues = array();
			
			if ( $form_check['inputs_without_labels'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of inputs without labels */
					__( '%d form inputs without proper <label> associations', 'wpshadow' ),
					$form_check['inputs_without_labels']
				);
			}

			if ( $form_check['placeholder_only_forms'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of forms */
					__( '%d forms using placeholder-only labels (accessibility anti-pattern)', 'wpshadow' ),
					$form_check['placeholder_only_forms']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/form-label-association-validation',
				'meta'         => array(
					'total_forms'             => $form_check['total_forms'],
					'inputs_without_labels'   => $form_check['inputs_without_labels'],
					'placeholder_only_forms'  => $form_check['placeholder_only_forms'],
					'missing_fieldsets'       => $form_check['missing_fieldsets'],
				),
			);
		}

		return null;
	}

	/**
	 * Validate form label associations.
	 *
	 * @since  1.26028.1905
	 * @return array Validation results.
	 */
	private static function validate_form_labels() {
		global $wpdb;

		$validation = array(
			'total_forms'            => 0,
			'inputs_without_labels'  => 0,
			'placeholder_only_forms' => 0,
			'missing_fieldsets'      => 0,
			'total_issues'           => 0,
		);

		// Sample recent posts with forms.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_content
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				AND post_content LIKE %s
				ORDER BY post_date DESC
				LIMIT 30",
				'publish',
				'%<form%'
			)
		);

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			
			// Extract forms.
			preg_match_all( '/<form[^>]*>.*?<\/form>/is', $content, $form_matches );
			
			if ( ! empty( $form_matches[0] ) ) {
				foreach ( $form_matches[0] as $form_html ) {
					++$validation['total_forms'];

					$form_check = self::analyze_form( $form_html );
					
					$validation['inputs_without_labels'] += $form_check['inputs_without_labels'];
					
					if ( $form_check['placeholder_only'] ) {
						++$validation['placeholder_only_forms'];
					}

					if ( $form_check['missing_fieldset'] ) {
						++$validation['missing_fieldsets'];
					}
				}
			}
		}

		$validation['total_issues'] = $validation['inputs_without_labels'] + $validation['placeholder_only_forms'];

		return $validation;
	}

	/**
	 * Analyze individual form.
	 *
	 * @since  1.26028.1905
	 * @param  string $form_html Form HTML.
	 * @return array Analysis results.
	 */
	private static function analyze_form( $form_html ) {
		$analysis = array(
			'inputs_without_labels' => 0,
			'placeholder_only'      => false,
			'missing_fieldset'      => false,
		);

		// Find all inputs.
		preg_match_all( '/<input[^>]+>/i', $form_html, $input_matches );
		
		if ( ! empty( $input_matches[0] ) ) {
			$has_placeholder = false;
			$has_label = false;

			foreach ( $input_matches[0] as $input ) {
				// Skip submit buttons and hidden inputs.
				if ( preg_match( '/type=["\']?(submit|hidden|button)["\']?/i', $input ) ) {
					continue;
				}

				// Check for id attribute.
				preg_match( '/id=["\']([^"\']+)["\']/i', $input, $id_match );
				
				if ( ! empty( $id_match[1] ) ) {
					$input_id = $id_match[1];
					
					// Check if there's a corresponding label.
					if ( false === strpos( $form_html, "for=\"{$input_id}\"" ) && 
					     false === strpos( $form_html, "for='{$input_id}'" ) ) {
						++$analysis['inputs_without_labels'];
					} else {
						$has_label = true;
					}
				} else {
					// No ID means no proper label association possible.
					++$analysis['inputs_without_labels'];
				}

				// Check for placeholder.
				if ( preg_match( '/placeholder=/i', $input ) ) {
					$has_placeholder = true;
				}
			}

			// If has placeholders but no proper labels, it's placeholder-only.
			if ( $has_placeholder && ! $has_label ) {
				$analysis['placeholder_only'] = true;
			}
		}

		// Check for radio/checkbox groups without fieldset.
		if ( preg_match( '/type=["\']?(radio|checkbox)["\']?/i', $form_html ) ) {
			if ( false === strpos( $form_html, '<fieldset' ) ) {
				$analysis['missing_fieldset'] = true;
			}
		}

		return $analysis;
	}
}
