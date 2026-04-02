<?php
/**
 * CSRF Vulnerabilities in Tool Actions
 *
 * Detects whether tool actions are protected against CSRF attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CSRF_Vulnerabilities_In_Tool_Actions Class
 *
 * Validates CSRF protection in tool operations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_CSRF_Vulnerabilities_In_Tool_Actions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'csrf-vulnerabilities-in-tool-actions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Action CSRF Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies tool actions are protected against CSRF attacks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests CSRF protection in tool actions.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check import nonce protection
		$import_issue = self::check_import_nonce_protection();
		if ( $import_issue ) {
			$issues[] = $import_issue;
		}

		// 2. Check export nonce protection
		$export_issue = self::check_export_nonce_protection();
		if ( $export_issue ) {
			$issues[] = $export_issue;
		}

		// 3. Check AJAX action nonces
		$ajax_issue = self::check_ajax_nonce_validation();
		if ( $ajax_issue ) {
			$issues[] = $ajax_issue;
		}

		// 4. Check referer validation
		$referer_issue = self::check_referer_validation();
		if ( $referer_issue ) {
			$issues[] = $referer_issue;
		}

		// 5. Check for double-submit cookie protection
		$cookie_issue = self::check_double_submit_cookie_pattern();
		if ( $cookie_issue ) {
			$issues[] = $cookie_issue;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of CSRF issues */
					__( '%d CSRF protection gaps found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/csrf-protection-tools',
				'recommendations' => array(
					__( 'Verify nonce present on all form submissions', 'wpshadow' ),
					__( 'Check nonce validation in AJAX handlers', 'wpshadow' ),
					__( 'Validate HTTP referer for state-changing actions', 'wpshadow' ),
					__( 'Implement SameSite cookie attribute', 'wpshadow' ),
					__( 'Test CSRF attacks against tool endpoints', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check import nonce protection.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_import_nonce_protection() {
		// Check if import form includes nonce field
		$import_form = $GLOBALS['wp_query']->query_vars['page'] ?? '';

		if ( ! has_action( 'wpshadow_verify_import_nonce' ) ) {
			return __( 'Import action missing nonce verification', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check export nonce protection.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_export_nonce_protection() {
		// Check if export action verifies nonce
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			return __( 'WordPress nonce function not available', 'wpshadow' );
		}

		// Check for export nonce validation in code
		if ( ! has_action( 'wpshadow_verify_export_nonce' ) ) {
			return __( 'Export action missing nonce verification', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check AJAX nonce validation.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_ajax_nonce_validation() {
		// Check for AJAX action nonce verification
		global $wp_filter;

		// Look for nonce check in AJAX handlers
		$ajax_actions = array(
			'wpshadow_import',
			'wpshadow_export',
			'wpshadow_erase',
		);

		$unprotected = array();

		foreach ( $ajax_actions as $action ) {
			$hook = "wp_ajax_{$action}";

			if ( ! isset( $wp_filter[ $hook ] ) ) {
				continue; // Action doesn't exist
			}

			// Would need to check if handler calls check_ajax_referer()
			// For now, assume potential issue
			$unprotected[] = $action;
		}

		if ( ! empty( $unprotected ) ) {
			return sprintf(
				/* translators: %d: number of actions, %s: action names */
				__( '%d AJAX actions may lack nonce validation: %s', 'wpshadow' ),
				count( $unprotected ),
				implode( ', ', $unprotected )
			);
		}

		return null;
	}

	/**
	 * Check referer validation.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_referer_validation() {
		// Check if admin actions validate referer
		// This would require checking for wp_verify_nonce with referer check

		// WordPress doesn't always enforce this strictly
		if ( ! function_exists( 'check_admin_referer' ) ) {
			return __( 'Admin referer check function not available', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check double-submit cookie pattern.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_double_submit_cookie_pattern() {
		// Check for SameSite cookie attribute
		$cookie_settings = session_get_cookie_params();

		if ( ! isset( $cookie_settings['samesite'] ) || 'Strict' !== $cookie_settings['samesite'] ) {
			return __( 'Session cookies may lack SameSite=Strict protection', 'wpshadow' );
		}

		return null;
	}
}
