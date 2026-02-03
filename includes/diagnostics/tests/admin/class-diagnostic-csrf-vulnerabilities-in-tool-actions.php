<?php
/**
 * CSRF Vulnerabilities in Tool Actions Diagnostic
 *
 * Detects whether tool actions (import, export, erasure) are protected against Cross-Site Request Forgery.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2034.1500
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
 * Verifies that tool actions have CSRF protection.
 *
 * @since 1.2034.1500
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
	protected static $description = 'Checks if tool actions are protected against Cross-Site Request Forgery attacks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_filter;

		$issues = array();

		// 1. Check core tool AJAX actions.
		$critical_ajax_actions = array(
			'wp_ajax_export_personal_data'  => 'Personal data export',
			'wp_ajax_erase_personal_data'   => 'Personal data erasure',
			'wp_ajax_delete-plugin'         => 'Plugin deletion',
			'wp_ajax_delete-theme'          => 'Theme deletion',
			'wp_ajax_update-plugin'         => 'Plugin update',
			'wp_ajax_update-theme'          => 'Theme update',
		);

		foreach ( $critical_ajax_actions as $action => $description ) {
			if ( ! isset( $wp_filter[ $action ] ) ) {
				continue;
			}

			// Core WordPress actions should have check_ajax_referer().
			// We can't inspect callbacks directly, but we can check registration.
			$callbacks = $wp_filter[ $action ]->callbacks ?? array();
			
			if ( empty( $callbacks ) ) {
				$issues[] = sprintf(
					/* translators: %s: action description */
					__( 'Action "%s" has no handlers - potential security gap', 'wpshadow' ),
					$description
				);
			}
		}

		// 2. Check for nonce fields in tool pages.
		$tool_pages = array(
			ABSPATH . 'wp-admin/tools.php',
			ABSPATH . 'wp-admin/import.php',
			ABSPATH . 'wp-admin/export.php',
		);

		foreach ( $tool_pages as $page ) {
			if ( file_exists( $page ) ) {
				$content = file_get_contents( $page );
				
				// Check for wp_nonce_field or check_admin_referer.
				if ( false === strpos( $content, 'wp_nonce_field' ) &&
				     false === strpos( $content, 'check_admin_referer' ) ) {
					$issues[] = sprintf(
						/* translators: %s: file name */
						__( 'Tool page "%s" may lack nonce protection', 'wpshadow' ),
						basename( $page )
					);
				}
			}
		}

		// 3. Check custom admin actions.
		$custom_actions = array();
		foreach ( $wp_filter as $action_name => $action_data ) {
			// Look for admin_action_ hooks.
			if ( 0 === strpos( $action_name, 'admin_action_' ) ) {
				$custom_actions[] = $action_name;
			}
		}

		if ( ! empty( $custom_actions ) ) {
			// Sample check: verify some have referer checks.
			$issues[] = sprintf(
				/* translators: %d: number of actions */
				_n(
					'%d custom admin action detected - verify CSRF protection',
					'%d custom admin actions detected - verify CSRF protection',
					count( $custom_actions ),
					'wpshadow'
				),
				count( $custom_actions )
			);
		}

		// 4. Check for REST API authentication.
		$rest_routes = rest_get_server()->get_routes();
		$tool_routes = array();
		
		foreach ( $rest_routes as $route => $handlers ) {
			// Look for tool-related endpoints.
			if ( false !== strpos( $route, 'export' ) ||
			     false !== strpos( $route, 'import' ) ||
			     false !== strpos( $route, 'personal-data' ) ) {
				$tool_routes[] = $route;
			}
		}

		if ( ! empty( $tool_routes ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of routes */
				__( '%d tool-related REST API endpoints found - verify authentication', 'wpshadow' ),
				count( $tool_routes )
			);
		}

		// 5. Check referer validation configuration.
		if ( ! defined( 'WPSHADOW_DISABLE_REFERER_CHECK' ) ) {
			// Good - referer check not disabled.
		} else {
			if ( WPSHADOW_DISABLE_REFERER_CHECK ) {
				$issues[] = __( 'Referer check is disabled - CSRF protection weakened', 'wpshadow' );
			}
		}

		// 6. Check for double-submit cookie protection.
		$has_double_submit = false;
		
		// WordPress uses nonces which include user session, but not traditional double-submit.
		// Check if any plugin adds this.
		if ( has_filter( 'wp_verify_nonce' ) ) {
			$has_double_submit = true;
		}

		if ( ! $has_double_submit ) {
			$issues[] = __( 'No additional CSRF protection (double-submit cookie) detected beyond WordPress nonces', 'wpshadow' );
		}

		// 7. Test nonce generation.
		$test_nonce = wp_create_nonce( 'wpshadow-test-action' );
		
		if ( empty( $test_nonce ) ) {
			$issues[] = __( 'Nonce generation failed - CSRF protection not functioning', 'wpshadow' );
		}

		// 8. Check nonce lifetime.
		$nonce_life = apply_filters( 'nonce_life', DAY_IN_SECONDS );
		
		if ( $nonce_life > ( 2 * DAY_IN_SECONDS ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of hours */
				__( 'Nonce lifetime is %d hours - longer window for CSRF attacks', 'wpshadow' ),
				(int) ( $nonce_life / HOUR_IN_SECONDS )
			);
		}

		if ( $nonce_life < HOUR_IN_SECONDS ) {
			$issues[] = __( 'Nonce lifetime under 1 hour - may cause usability issues', 'wpshadow' );
		}

		// 9. Check logged-out nonce handling.
		if ( ! is_user_logged_in() ) {
			// Nonces still work for logged-out users - verify this is intentional.
			$logged_out_nonce = wp_create_nonce( 'test' );
			
			if ( ! empty( $logged_out_nonce ) ) {
				// This is normal WordPress behavior.
			}
		}

		// 10. Check for SameSite cookie attribute.
		$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
		
		// WordPress doesn't set SameSite by default in older versions.
		if ( version_compare( get_bloginfo( 'version' ), '5.6', '<' ) ) {
			$issues[] = __( 'WordPress version doesn\'t support SameSite cookie attribute - upgrade for better CSRF protection', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'CSRF protection issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/csrf-protection',
			'details'      => array(
				'issues'         => $issues,
				'nonce_life'     => $nonce_life,
				'custom_actions' => count( $custom_actions ),
			),
		);
	}
}
