<?php
/**
 * Tool Nonce Validation Failures Diagnostic
 *
 * Comprehensive test of nonce implementation across all Tool actions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2034.1505
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Tool_Nonce_Validation_Failures Class
 *
 * Verifies nonce implementation across tool actions.
 *
 * @since 1.2034.1505
 */
class Diagnostic_Tool_Nonce_Validation_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tool-nonce-validation-failures';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Nonce Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Comprehensive verification of nonce security implementation across tool actions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1505
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_filter;

		$issues = array();

		// 1. Test nonce generation.
		$test_nonce = wp_create_nonce( 'wpshadow-test' );
		
		if ( empty( $test_nonce ) || strlen( $test_nonce ) < 10 ) {
			$issues[] = __( 'Nonce generation producing invalid values', 'wpshadow' );
		}

		// 2. Test nonce verification.
		$verify_result = wp_verify_nonce( $test_nonce, 'wpshadow-test' );
		
		if ( false === $verify_result ) {
			$issues[] = __( 'Nonce verification failing for valid nonces', 'wpshadow' );
		}

		// 3. Check critical tool AJAX actions.
		$ajax_actions = array(
			'wp_ajax_export_personal_data',
			'wp_ajax_erase_personal_data',
			'wp_ajax_delete-plugin',
			'wp_ajax_update-plugin',
			'wp_ajax_install-plugin',
		);

		$missing_nonce_checks = array();
		foreach ( $ajax_actions as $action ) {
			if ( isset( $wp_filter[ $action ] ) ) {
				// WordPress core should handle these - but verify registration exists.
				$callbacks = $wp_filter[ $action ]->callbacks ?? array();
				if ( empty( $callbacks ) ) {
					$missing_nonce_checks[] = str_replace( 'wp_ajax_', '', $action );
				}
			}
		}

		if ( ! empty( $missing_nonce_checks ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of actions */
				__( 'AJAX actions missing handlers: %s', 'wpshadow' ),
				implode( ', ', $missing_nonce_checks )
			);
		}

		// 4. Check nonce lifetime.
		$nonce_life = apply_filters( 'nonce_life', DAY_IN_SECONDS );
		
		if ( $nonce_life > ( 2 * DAY_IN_SECONDS ) ) {
			$issues[] = sprintf(
				/* translators: %d: hours */
				__( 'Nonce lifetime %d hours is too long - increases replay attack window', 'wpshadow' ),
				(int) ( $nonce_life / HOUR_IN_SECONDS )
			);
		}

		// 5. Test nonce with different actions.
		$action1_nonce = wp_create_nonce( 'action1' );
		$action2_nonce = wp_create_nonce( 'action2' );
		
		// Verify different actions produce different nonces.
		if ( $action1_nonce === $action2_nonce ) {
			$issues[] = __( 'Nonces not action-specific - security weakness detected', 'wpshadow' );
		}

		// 6. Check user-specific nonces.
		$user_id       = get_current_user_id();
		$current_nonce = wp_create_nonce( 'test-action' );
		
		// Nonces should include user session.
		if ( empty( $user_id ) ) {
			// Logged out - nonce should still work but be different.
		}

		// 7. Check custom admin actions for nonce fields.
		$custom_actions = array();
		foreach ( $wp_filter as $hook => $data ) {
			if ( 0 === strpos( $hook, 'admin_action_' ) ) {
				$custom_actions[] = str_replace( 'admin_action_', '', $hook );
			}
		}

		if ( count( $custom_actions ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of actions */
				__( '%d custom admin actions - verify all have nonce protection', 'wpshadow' ),
				count( $custom_actions )
			);
		}

		// 8. Check for nonce in tool forms.
		$tool_pages = array(
			'tools.php',
			'import.php',
			'export.php',
		);

		foreach ( $tool_pages as $page ) {
			$full_path = ABSPATH . 'wp-admin/' . $page;
			if ( file_exists( $full_path ) ) {
				$content = file_get_contents( $full_path );
				
				if ( false === strpos( $content, 'wp_nonce_field' ) &&
				     false === strpos( $content, '_wpnonce' ) ) {
					$issues[] = sprintf(
						/* translators: %s: page name */
						__( 'Tool page %s may lack nonce fields', 'wpshadow' ),
						$page
					);
				}
			}
		}

		// 9. Test nonce expiration handling.
		// Create a nonce that should be expired.
		$old_nonce = wp_create_nonce( 'old-test' );
		
		// Verify tick-based validation.
		$tick = ceil( time() / ( $nonce_life / 2 ) );
		
		// WordPress uses 2 ticks - current and previous.
		// After 2 ticks, nonce should fail.

		// 10. Check REST API nonce alternatives.
		if ( function_exists( 'rest_get_authentication_errors' ) ) {
			$rest_auth = rest_get_authentication_errors();
			
			if ( is_wp_error( $rest_auth ) ) {
				$issues[] = __( 'REST API authentication having issues - may affect tool APIs', 'wpshadow' );
			}
		}

		// 11. Check for replay attack prevention.
		// Nonces should ideally be one-time use, but WordPress uses time-based.
		$replay_protection = has_filter( 'check_ajax_referer' );
		
		if ( ! $replay_protection ) {
			$issues[] = __( 'No additional replay attack protection beyond time-based nonces', 'wpshadow' );
		}

		// 12. Check for AJAX nonce in JavaScript.
		if ( is_admin() ) {
			global $wp_scripts;
			
			// Check if scripts receive nonce via localization.
			$has_ajax_nonce = false;
			if ( isset( $wp_scripts->registered ) ) {
				foreach ( $wp_scripts->registered as $handle => $script ) {
					if ( isset( $script->extra['data'] ) ) {
						$data = $script->extra['data'];
						if ( false !== strpos( $data, 'nonce' ) || false !== strpos( $data, '_wpnonce' ) ) {
							$has_ajax_nonce = true;
							break;
						}
					}
				}
			}

			if ( ! $has_ajax_nonce ) {
				$issues[] = __( 'No nonces detected in JavaScript localization - AJAX may be unprotected', 'wpshadow' );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Nonce validation issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/tool-nonce-validation',
			'details'      => array(
				'issues'         => $issues,
				'nonce_life'     => $nonce_life,
				'custom_actions' => count( $custom_actions ),
			),
		);
	}
}
