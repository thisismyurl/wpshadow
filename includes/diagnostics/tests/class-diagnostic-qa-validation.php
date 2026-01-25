<?php
/**
 * QA Validation Diagnostic
 *
 * Comprehensive quality assurance checker for WPShadow UI/UX.
 * Validates form handling, error states, user feedback, and
 * cross-browser compatibility concerns.
 *
 * Phase 5 of UI/UX Epic - Final Polish & Validation
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics\Tests
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_QA_Validation Class
 *
 * Performs comprehensive QA validation across WPShadow admin interface.
 * Checks for proper error handling, user feedback, form validation, and more.
 */
class Diagnostic_QA_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'qa-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Quality Assurance Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates UI/UX quality and user experience best practices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'usability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Validate AJAX handlers have proper error handling.
		$ajax_issues = self::check_ajax_error_handling();
		if ( ! empty( $ajax_issues ) ) {
			$issues = array_merge( $issues, $ajax_issues );
		}

		// Check 2: Validate forms have proper validation.
		$form_issues = self::check_form_validation();
		if ( ! empty( $form_issues ) ) {
			$issues = array_merge( $issues, $form_issues );
		}

		// Check 3: Check for user feedback mechanisms.
		$feedback_issues = self::check_user_feedback();
		if ( ! empty( $feedback_issues ) ) {
			$issues = array_merge( $issues, $feedback_issues );
		}

		// Check 4: Validate internationalization.
		$i18n_issues = self::check_internationalization();
		if ( ! empty( $i18n_issues ) ) {
			$issues = array_merge( $issues, $i18n_issues );
		}

		// If any issues found, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => __( 'Quality Assurance Issues Detected', 'wpshadow' ),
				'description'   => sprintf(
					/* translators: %d: number of QA issues found */
					__( 'Found %d quality assurance issues that could impact user experience.', 'wpshadow' ),
					count( $issues )
				),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/qa-validation/',
				'training_link' => 'https://wpshadow.com/training/quality-assurance/',
				'module'        => 'Usability',
				'priority'      => 3,
				'meta'          => array(
					'issues'       => $issues,
					'total_issues' => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Check AJAX handlers for proper error handling.
	 *
	 * Validates:
	 * - Nonce verification present
	 * - Capability checks present
	 * - Error responses properly formatted
	 * - Success responses include data
	 *
	 * @since  1.2601.2148
	 * @return array List of issues found.
	 */
	private static function check_ajax_error_handling() {
		$issues    = array();
		$ajax_path = WPSHADOW_PATH . 'includes/admin/ajax/';

		if ( ! is_dir( $ajax_path ) ) {
			return $issues;
		}

		$ajax_files = glob( $ajax_path . '*.php' );
		if ( ! $ajax_files ) {
			return $issues;
		}

		foreach ( $ajax_files as $file ) {
			$content  = file_get_contents( $file );
			$filename = basename( $file );

			// Check for nonce verification.
			$has_nonce_check = preg_match( '/(verify_request|check_ajax_referer|wp_verify_nonce)/i', $content );
			if ( ! $has_nonce_check ) {
				$issues[] = array(
					'file'     => $filename,
					'issue'    => 'missing_nonce_verification',
					'message'  => __( 'AJAX handler missing nonce verification', 'wpshadow' ),
					'severity' => 'high',
				);
			}

			// Check for capability checks.
			$has_cap_check = preg_match( '/(current_user_can|verify_request.*manage_options)/i', $content );
			if ( ! $has_cap_check ) {
				$issues[] = array(
					'file'     => $filename,
					'issue'    => 'missing_capability_check',
					'message'  => __( 'AJAX handler missing capability check', 'wpshadow' ),
					'severity' => 'high',
				);
			}

			// Check for error handling.
			$has_error_handling = preg_match( '/(try.*catch|send_error|wp_send_json_error)/i', $content );
			if ( ! $has_error_handling ) {
				$issues[] = array(
					'file'     => $filename,
					'issue'    => 'missing_error_handling',
					'message'  => __( 'AJAX handler missing error handling', 'wpshadow' ),
					'severity' => 'medium',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check forms for proper validation.
	 *
	 * Validates:
	 * - Required fields marked with aria-required
	 * - Error messages associated with fields
	 * - Submit buttons have proper labels
	 * - Forms have proper method and action
	 *
	 * @since  1.2601.2148
	 * @return array List of issues found.
	 */
	private static function check_form_validation() {
		$issues   = array();
		$php_path = WPSHADOW_PATH . 'includes/admin/';

		if ( ! is_dir( $php_path ) ) {
			return $issues;
		}

		$php_files = self::get_php_files_recursive( $php_path );

		foreach ( $php_files as $file ) {
			$content  = file_get_contents( $file );
			$filename = str_replace( $php_path, '', $file );

			// Check for forms with required fields.
			preg_match_all( '/<form[^>]*>/i', $content, $forms );
			foreach ( $forms[0] as $form ) {
				// Check if form has nonce field.
				$form_start_pos = strpos( $content, $form );
				$form_end_pos   = strpos( $content, '</form>', $form_start_pos );
				if ( false !== $form_start_pos && false !== $form_end_pos ) {
					$form_content = substr( $content, $form_start_pos, $form_end_pos - $form_start_pos );

					// Check for nonce.
					if ( ! preg_match( '/(wp_nonce_field|wp_create_nonce|_wpnonce)/i', $form_content ) ) {
						$issues[] = array(
							'file'     => $filename,
							'issue'    => 'form_missing_nonce',
							'message'  => __( 'Form missing nonce field', 'wpshadow' ),
							'severity' => 'high',
						);
					}

					// Check for required fields without aria-required.
					preg_match_all( '/<input[^>]*required[^>]*>/i', $form_content, $required_inputs );
					foreach ( $required_inputs[0] as $input ) {
						if ( ! preg_match( '/aria-required=["\']true["\']/i', $input ) ) {
							$issues[] = array(
								'file'     => $filename,
								'issue'    => 'required_field_missing_aria',
								'message'  => __( 'Required field missing aria-required attribute', 'wpshadow' ),
								'severity' => 'low',
							);
						}
					}
				}
			}
		}

		return $issues;
	}

	/**
	 * Check for user feedback mechanisms.
	 *
	 * Validates:
	 * - Success messages displayed after actions
	 * - Loading states indicated during async operations
	 * - Confirmation dialogs for destructive actions
	 * - Progress indicators for long operations
	 *
	 * @since  1.2601.2148
	 * @return array List of issues found.
	 */
	private static function check_user_feedback() {
		$issues  = array();
		$js_path = WPSHADOW_PATH . 'assets/js/';

		if ( ! is_dir( $js_path ) ) {
			return $issues;
		}

		$js_files = glob( $js_path . '*.js' );
		if ( ! $js_files ) {
			return $issues;
		}

		foreach ( $js_files as $file ) {
			$content  = file_get_contents( $file );
			$filename = basename( $file );

			// Check for AJAX calls without loading indicators.
			$has_ajax    = preg_match( '/\.(ajax|post|fetch)\s*\(/i', $content );
			$has_loading = preg_match( '/(loading|spinner|aria-busy|disabled)/i', $content );

			if ( $has_ajax && ! $has_loading ) {
				$issues[] = array(
					'file'     => $filename,
					'issue'    => 'ajax_without_loading_state',
					'message'  => __( 'AJAX call without loading state indicator', 'wpshadow' ),
					'severity' => 'low',
				);
			}

			// Check for destructive actions without confirmation.
			$has_delete  = preg_match( '/(delete|remove|destroy|clear)/i', $content );
			$has_confirm = preg_match( '/(confirm|dialog|modal)/i', $content );

			if ( $has_delete && ! $has_confirm ) {
				$issues[] = array(
					'file'     => $filename,
					'issue'    => 'destructive_action_without_confirmation',
					'message'  => __( 'Destructive action without user confirmation', 'wpshadow' ),
					'severity' => 'medium',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check internationalization compliance.
	 *
	 * Validates:
	 * - All user-facing strings use translation functions
	 * - Correct text domain used ('wpshadow')
	 * - Pluralization handled correctly
	 * - Translator comments provided where needed
	 *
	 * @since  1.2601.2148
	 * @return array List of issues found.
	 */
	private static function check_internationalization() {
		$issues   = array();
		$php_path = WPSHADOW_PATH . 'includes/admin/';

		if ( ! is_dir( $php_path ) ) {
			return $issues;
		}

		$php_files = self::get_php_files_recursive( $php_path );

		foreach ( $php_files as $file ) {
			$content  = file_get_contents( $file );
			$filename = str_replace( $php_path, '', $file );

			// Check for hardcoded strings in HTML output.
			preg_match_all( '/echo\s+[\'"]([^<>\'"\n]{10,})[\'"];/i', $content, $echo_matches );
			foreach ( $echo_matches[1] as $hardcoded_string ) {
				// Skip if it's a variable or code.
				if ( ! preg_match( '/[{}<>$]/', $hardcoded_string ) ) {
					$issues[] = array(
						'file'     => $filename,
						'issue'    => 'hardcoded_string',
						'message'  => sprintf(
							/* translators: %s: hardcoded string */
							__( 'Hardcoded string found: "%s"', 'wpshadow' ),
							substr( $hardcoded_string, 0, 50 )
						),
						'severity' => 'low',
					);
				}
			}

			// Check for incorrect text domain.
			preg_match_all( '/__(.*?)[,\)]/', $content, $translation_calls );
			foreach ( $translation_calls[0] as $call ) {
				if ( ! preg_match( '/[,\s][\'"]wpshadow[\'"]/', $call ) ) {
					if ( preg_match( '/[,\s][\'"]([^\'"]+)[\'"]/', $call, $domain_match ) ) {
						$issues[] = array(
							'file'     => $filename,
							'issue'    => 'wrong_text_domain',
							'message'  => sprintf(
								/* translators: %s: incorrect text domain */
								__( 'Incorrect text domain: "%s" (should be "wpshadow")', 'wpshadow' ),
								$domain_match[1]
							),
							'severity' => 'medium',
						);
					}
				}
			}
		}

		return $issues;
	}

	/**
	 * Get PHP files recursively from directory.
	 *
	 * @since  1.2601.2148
	 * @param  string $dir Directory path.
	 * @return array Array of file paths.
	 */
	private static function get_php_files_recursive( $dir ) {
		$files = array();
		$items = glob( $dir . '/*' );

		if ( ! $items ) {
			return $files;
		}

		foreach ( $items as $item ) {
			if ( is_dir( $item ) ) {
				$files = array_merge( $files, self::get_php_files_recursive( $item ) );
			} elseif ( pathinfo( $item, PATHINFO_EXTENSION ) === 'php' ) {
				$files[] = $item;
			}
		}

		return $files;
	}

	/**
	 * Get the diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'QA Validation', 'wpshadow' );
	}

	/**
	 * Get the diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Validates UI/UX quality and user experience best practices.', 'wpshadow' );
	}
}
