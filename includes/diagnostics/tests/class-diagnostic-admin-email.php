<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Admin_Email extends Diagnostic_Base {


	protected static $slug        = 'admin-email';
	protected static $title       = 'Admin Email Configuration';
	protected static $description = 'Checks if admin email is valid and configured.';

	public static function check(): ?array {
		$admin_email = get_option( 'admin_email' );

		if ( empty( $admin_email ) ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin email is not configured. WordPress sends critical notifications to this address including security alerts, update notifications, and user registration confirmations.', 'wpshadow' ),
				'category'     => 'settings',
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		if ( false === filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Admin email "%s" is not a valid email address. You will not receive important notifications about your site.', 'wpshadow' ),
					esc_html( $admin_email )
				),
				'category'     => 'settings',
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		if ( strpos( $admin_email, 'example.com' ) !== false || strpos( $admin_email, 'test.com' ) !== false ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Admin email appears to be a placeholder (%s). Set a real, monitored email address.', 'wpshadow' ),
					esc_html( $admin_email )
				),
				'category'     => 'settings',
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		// Check if using default WordPress from email
		$wp_from_email = 'wordpress@' . preg_replace( '#^www\.#', '', wp_parse_url( home_url(), PHP_URL_HOST ) );
		$from_email    = get_option( 'wpshadow_email_from_email', '' );

		if ( empty( $from_email ) || $from_email === $wp_from_email ) {
			return array(
				'finding_id'   => self::$slug . '-from',
				'title'        => __( 'Email From Address Not Configured', 'wpshadow' ),
				'description'  => sprintf(
					__( 'Your site is using the default WordPress from address (%s). Many email providers will reject emails from this address, causing delivery failures. Configure a proper from email address using the Email Test tool under WPShadow → Tools.', 'wpshadow' ),
					esc_html( $wp_from_email )
				),
				'category'     => 'settings',
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Admin Email Configuration
	 * Slug: admin-email
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if admin email is valid and configured.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_admin_email(): array {
		$admin_email   = get_option( 'admin_email' );
		$wp_from_email = 'wordpress@' . preg_replace( '#^www\\.#', '', wp_parse_url( home_url(), PHP_URL_HOST ) );
		$from_email    = get_option( 'wpshadow_email_from_email', '' );

		// Determine if issue exists
		$is_empty         = empty( $admin_email );
		$is_invalid       = ( ! $is_empty && false === filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) );
		$is_placeholder   = ( ! $is_empty && ( strpos( $admin_email, 'example.com' ) !== false || strpos( $admin_email, 'test.com' ) !== false ) );
		$from_email_issue = ( empty( $from_email ) || $from_email === $wp_from_email );

		$has_issue = ( $is_empty || $is_invalid || $is_placeholder || $from_email_issue );

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );

		$test_passes = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Admin email check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (email: %s, valid: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$admin_email ? $admin_email : 'empty',
				$is_invalid ? 'no' : 'yes'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
